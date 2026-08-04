<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/producto_caducidad.php';
require_once __DIR__ . '/../core/cache_respuesta.php';
require_once __DIR__ . '/../core/orders_schema.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$items    = $input['items'] ?? [];

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

$paypalId = $input['paypal_id'] ?? null;
$status   = $input['status'] ?? 'PENDING';
$total    = isset($input['total']) ? floatval($input['total']) : 0.0;
$delivery = $input['delivery'] ?? 'domicilio';
$address  = $input['address'] ?? [];
$schedule = $input['schedule'] ?? [];
$items    = $input['items'] ?? [];
  $user     = $input['user'] ?? [];
  $payMethod= 'paypal';

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

  try {
  $userId    = isset($user['id']) ? intval($user['id']) : 0;
  $userEmail = $user['email'] ?? null;
  $userName  = $user['name'] ?? null;

  if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    $has = false;
    try {
      $stC = $conexion->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = 'usuario' AND column_name = 'email_verified' LIMIT 1");
      $stC->execute();
      $has = (bool)$stC->fetchColumn();
      $stC->closeCursor();
    } catch (Throwable $_) { $has = false; }

    if ($has) {
      $stU = $conexion->prepare("SELECT email_verified FROM usuario WHERE correo_electronico = ? LIMIT 1");
      $stU->execute([$userEmail]);
      $row = $stU->fetch(PDO::FETCH_ASSOC);
      $stU->closeCursor();
      if (!$row) {
        echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado', 'code' => 'user_not_found']);
        exit;
      }
      if (!(bool)($row['email_verified'] ?? false)) {
        echo json_encode(['ok' => false, 'error' => 'Debes verificar tu correo para poder comprar', 'code' => 'email_not_verified']);
        exit;
      }
    }
  }

  // Garantizar esquema completo y consistente (creado por cualquier otro endpoint)
  ensure_orders_schema($conexion);

  $stmt = $conexion->prepare("
    INSERT INTO orders_pg (user_id, paypal_id, user_email, user_name, status, total, delivery_method, pay_method, address_json, schedule_json)
    VALUES (?,?,?,?,?,?,?,?,?::jsonb,?::jsonb)
    RETURNING id
  ");
  $stmt->execute([$userId ?: null, $paypalId, $userEmail, $userName, $status, $total, $delivery, $payMethod, json_encode($address), json_encode($schedule)]);
  $orderId = intval($stmt->fetchColumn());
  $stmt->closeCursor();

  if (is_array($items)) {
    $ins = $conexion->prepare('INSERT INTO order_items_pg (order_id, title, price, qty, image) VALUES (?,?,?,?,?)');
    foreach ($items as $it) {
      $title = $it['title'] ?? ($it['name'] ?? 'Producto');
      $productId = isset($it['id']) ? intval($it['id']) : 0;
      $price = ($productId > 0 && isset($verifiedPrices[$productId])) ? $verifiedPrices[$productId] : 0;
      $qty   = intval($it['qty'] ?? ($it['quantity'] ?? 1));
      $img   = $it['image'] ?? ($it['img'] ?? null);
      $ins->execute([$orderId, $title, $price, $qty, $img]);
      
      // Disminuir el stock del producto
      if ($productId > 0) {
        try {
          $stockStmt = $conexion->prepare("UPDATE producto SET stock = stock - ? WHERE id_producto = ? AND stock >= ?");
          $stockStmt->execute([$qty, $productId, $qty]);
        } catch (Throwable $e) {
          // Ignorar errores de stock, continuar con la compra
        }
      }
    }
  }

  cache_respuesta_invalidar();

  // Registrar notificación de admin
  try {
    ensure_notificaciones_schema($conexion);
    $statusNorm = strtoupper((string)$status);
    if ($statusNorm === 'COMPLETED') {
      record_notificacion(
        $conexion,
        'sale',
        "Venta registrada #$orderId",
        "$userEmail realizó una compra de $" . number_format($total, 2),
        $orderId,
        $paypalId
      );
    } else {
      record_notificacion(
        $conexion,
        'order',
        "Nuevo pedido #$orderId",
        "$userEmail realizó un pedido",
        $orderId,
        $paypalId
      );
    }
    cache_respuesta_invalidar();
  } catch (Throwable $_) {}

  echo json_encode(['ok'=>true, 'order_id'=>$orderId]);
} catch (Throwable $e) {
  error_log('orders_save.php: ' . $e->getMessage());
  echo json_encode(['ok'=>false, 'error'=>'No se pudo procesar tu pedido. Intenta más tarde.']);
}
?> 
