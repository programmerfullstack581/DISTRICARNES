<?php
// =============================================
// Crea un pedido PENDING y devuelve los campos
// del formulario WebCheckout de PayU.
// El frontend construye el form y lo envía a la
// URL de PayU para redirigir al cliente.
// La confirmación real la hace payu_confirmation.php
// (notificación POST del servidor de PayU).
// =============================================
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/../core/payu_config.php';
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/producto_caducidad.php';
require_once __DIR__ . '/../core/cache_respuesta.php';
require_once __DIR__ . '/../core/orders_schema.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/rate_limit.php';
require_once __DIR__ . '/../core/whatsapp_sender.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

dc_csrf_require();

$rl = dc_rate_limit_consume('order:' . dc_client_ip(), 10, 600);
if (!$rl['allowed']) {
  http_response_code(429);
  echo json_encode(['ok'=>false, 'error'=>'Demasiados pedidos. Espera unos minutos e inténtalo de nuevo.', 'code'=>'rate_limited']);
  exit;
}

// Credenciales PayU (deben estar en variables de entorno)
if (!payu_is_configured()) {
  echo json_encode([
    'ok' => false,
    'error' => 'El pago con PayU aún no está configurado. Contacta al administrador.',
    'code' => 'payu_not_configured'
  ]);
  exit;
}

$delivery = $input['delivery'] ?? 'domicilio';
$address  = $input['address'] ?? [];
$schedule = $input['schedule'] ?? [];
$items    = $input['items'] ?? [];
$user     = $input['user'] ?? [];

// Se requiere correo del comprador para WebCheckout
$userEmail = isset($user['email']) ? trim((string)$user['email']) : '';
$userName  = isset($user['name']) ? trim((string)$user['name']) : '';
if (!$userEmail || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['ok'=>false, 'error'=>'Debes iniciar sesión para pagar con PayU', 'code'=>'login_required']);
  exit;
}

// Validar que el carrito tenga productos con cantidades válidas
if (!is_array($items) || count($items) === 0) {
  echo json_encode(['ok'=>false, 'error'=>'No hay productos en tu carrito', 'code'=>'empty_cart']);
  exit;
}
foreach ($items as $it) {
  $q = intval($it['qty'] ?? ($it['quantity'] ?? 1));
  if ($q < 1 || $q > 99) {
    echo json_encode(['ok'=>false, 'error'=>'Cantidad inválida en tu carrito', 'code'=>'invalid_qty']);
    exit;
  }
}

// Validar que ningún producto del carrito esté vencido
if (is_array($items)) {
  foreach ($items as $it) {
    $pid = isset($it['id']) ? intval($it['id']) : 0;
    if ($pid > 0 && producto_caducidad_es_vencido($conexion, $pid)) {
      echo json_encode(['ok'=>false, 'error'=>'Uno de los productos de tu carrito está vencido y no puede venderse. Retíralo para continuar.', 'code'=>'product_expired', 'product_id'=>$pid]);
      exit;
    }
  }
}

// Recalcular total usando precios reales de la BD (anti-manipulación)
$verifiedPrices = [];
if (is_array($items)) {
  $precioStmt = $conexion->prepare("SELECT precio_venta, stock FROM producto WHERE id_producto = ? LIMIT 1");
  foreach ($items as $it) {
    $pid = isset($it['id']) ? intval($it['id']) : 0;
    if ($pid > 0) {
      $precioStmt->execute([$pid]);
      $prod = $precioStmt->fetch(PDO::FETCH_ASSOC);
      $precioStmt->closeCursor();
      if (!$prod) {
        echo json_encode(['ok'=>false, 'error'=>'Uno de los productos de tu carrito ya no está disponible. Actualiza el carrito.', 'code'=>'product_not_found', 'product_id'=>$pid]);
        exit;
      }
      $q = intval($it['qty'] ?? ($it['quantity'] ?? 1));
      if (intval($prod['stock']) < $q) {
        echo json_encode(['ok'=>false, 'error'=>'No hay suficiente stock de uno de tus productos. Actualiza el carrito.', 'code'=>'insufficient_stock', 'product_id'=>$pid]);
        exit;
      }
      $verifiedPrices[$pid] = floatval($prod['precio_venta']);
    }
  }
}

$calculatedSubtotal = 0;
if (is_array($items)) {
  foreach ($items as $it) {
    $pid = isset($it['id']) ? intval($it['id']) : 0;
    $p = ($pid > 0 && isset($verifiedPrices[$pid])) ? $verifiedPrices[$pid] : 0;
    $q = intval($it['qty'] ?? ($it['quantity'] ?? 1));
    $calculatedSubtotal += ($p * $q);
  }
}

$FREE_SHIPPING_THRESHOLD = 10000;
$shippingCost = ($calculatedSubtotal >= $FREE_SHIPPING_THRESHOLD || $delivery === 'punto') ? 0 : 7000;
$total = $calculatedSubtotal + $shippingCost;

if ($total <= 0) {
  echo json_encode(['ok'=>false, 'error'=>'El total de la compra debe ser mayor a 0', 'code'=>'invalid_total']);
  exit;
}

// Referencia única por pedido
$referenceCode = 'DC' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));

try {
  $userId = isset($user['id']) ? intval($user['id']) : 0;

  // Garantizar esquema completo y consistente
  ensure_orders_schema($conexion);

  // Transacción: orden + items + descuento de stock deben ser atómicos.
  $conexion->beginTransaction();
  try {
    $stmt = $conexion->prepare("
      INSERT INTO orders_pg (user_id, paypal_id, reference_code, user_email, user_name, status, total, delivery_method, pay_method, address_json, schedule_json)
      VALUES (?,?,?,?,?,?,?,?,?,?::jsonb,?::jsonb)
      RETURNING id
    ");
    $stmt->execute([$userId ?: null, null, $referenceCode, $userEmail, $userName, 'PENDING', $total, $delivery, 'payu', json_encode($address), json_encode($schedule)]);
    $orderId = intval($stmt->fetchColumn());
    $stmt->closeCursor();

    if (is_array($items)) {
      $ins = $conexion->prepare('INSERT INTO order_items_pg (order_id, title, price, qty, image) VALUES (?,?,?,?,?)');
      $stockStmt = $conexion->prepare("UPDATE producto SET stock = stock - ? WHERE id_producto = ? AND stock >= ?");
      foreach ($items as $it) {
        $title = $it['title'] ?? ($it['name'] ?? 'Producto');
        $productId = isset($it['id']) ? intval($it['id']) : 0;
        $price = ($productId > 0 && isset($verifiedPrices[$productId])) ? $verifiedPrices[$productId] : 0;
        $qty   = intval($it['qty'] ?? ($it['quantity'] ?? 1));
        $img   = $it['image'] ?? ($it['img'] ?? null);
        $ins->execute([$orderId, $title, $price, $qty, $img]);

        if ($productId > 0) {
          $stockStmt->execute([$qty, $productId, $qty]);
          if ($stockStmt->rowCount() < 1) {
            throw new RuntimeException('Sin stock suficiente para el producto ' . $productId);
          }
        }
      }
    }
    $conexion->commit();
  } catch (Throwable $e) {
    if ($conexion->inTransaction()) {
      $conexion->rollBack();
    }
    throw $e;
  }

  cache_respuesta_invalidar();

  // Registrar notificación de admin (pedido pendiente de pago)
  try {
    ensure_notificaciones_schema($conexion);
    record_notificacion(
      $conexion,
      'order',
      "Pago PayU iniciado #$orderId",
      "$userEmail inició pago de $" . number_format($total, 0) . " (ref $referenceCode)",
      $orderId,
      $referenceCode
    );
    cache_respuesta_invalidar();
  } catch (Throwable $_) {}

  // Monto en pesos (COP no tiene decimales) — string que firma PayU
  $amount = (string)(int)round($total);

  // Campos del formulario WebCheckout
  $fields = [
    'merchantId'      => PAYU_MERCHANT_ID,
    'accountId'       => PAYU_ACCOUNT_ID,
    'description'     => 'Compra en DISTRICARNES HERMANOS NAVARRO — Orden #' . $orderId,
    'referenceCode'   => $referenceCode,
    'amount'          => $amount,
    'tax'             => PAYU_TAX,
    'taxReturnBase'   => PAYU_TAX_RETURN_BASE,
    'currency'        => PAYU_CURRENCY,
    'signature'       => payu_signature($referenceCode, $amount, PAYU_CURRENCY),
    'test'            => PAYU_ENV === 'live' ? '0' : '1',
    'buyerEmail'      => $userEmail,
    'responseUrl'     => payu_base_url() . '/checkout/payu_response.php',
    'confirmationUrl' => payu_base_url() . '/backend/php/payments/payu_confirmation.php',
    'extra1'          => (string)$orderId,
    'extra2'          => $userName,
    'extra3'          => 'WEB',
  ];

  echo json_encode([
    'ok' => true,
    'order_id' => $orderId,
    'reference' => $referenceCode,
    'amount' => $amount,
    'checkout' => [
      'action' => payu_checkout_url(),
      'method' => 'POST',
      'fields' => $fields,
    ],
  ]);
} catch (Throwable $e) {
  error_log('create_payu_order.php: ' . $e->getMessage());
  $isStock = (strpos($e->getMessage(), 'Sin stock suficiente') !== false);
  echo json_encode([
    'ok' => false,
    'error' => $isStock
      ? 'El stock cambió mientras finalizabas tu compra. Actualiza tu carrito e inténtalo de nuevo.'
      : 'No se pudo iniciar el pago. Intenta más tarde.',
    'code' => $isStock ? 'stock_changed' : 'save_error'
  ]);
}
?>
