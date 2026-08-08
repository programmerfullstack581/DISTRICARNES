<?php
// =============================================
// CONFIRMACIÓN PayU (WebCheckout)
// =============================================
// Recibe la notificación POST que PayU envía desde su servidor
// cuando cambia el estado de una transacción. Esta es la fuente
// AUTORITATIVA para marcar un pago como aprobado: la página de
// retorno del cliente (payu_response.php) NO actualiza la BD.
//
// Seguridad:
//   - Verifica que merchant_id coincida.
//   - Verifica la firma md5(ApiKey~merchantId~reference_sale~value~currency).
//   - En producción ignora notificaciones marcadas como test.
//   - Idempotente: no re-procesa estados ya aplicados.
// =============================================
require_once __DIR__ . '/../core/payu_config.php';
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/cache_respuesta.php';
require_once __DIR__ . '/../core/orders_schema.php';
require_once __DIR__ . '/../core/whatsapp_sender.php';

// Nota: este endpoint NO usa CSRF — es una notificación servidor a servidor.

function payu_conf_log(string $msg): void {
  error_log('[payu_confirmation] ' . $msg);
}

if (!payu_is_configured()) {
  payu_conf_log('PayU no configurado.');
  http_response_code(500);
  exit('PayU no configurado');
}

$params = $_POST;

$merchantId    = trim((string)($params['merchant_id'] ?? ''));
$referenceSale = trim((string)($params['reference_sale'] ?? ''));
$value         = trim((string)($params['value'] ?? ''));
$currency      = trim((string)($params['currency'] ?? ''));
$signature     = trim((string)($params['signature'] ?? ''));
$statePol      = trim((string)($params['state_pol'] ?? ''));
$transactionId = trim((string)($params['transaction_id'] ?? ''));
$test          = trim((string)($params['test'] ?? ''));
$responseMessage = trim((string)($params['response_message_pol'] ?? ''));

if ($referenceSale === '' || $statePol === '' || $signature === '') {
  payu_conf_log('Parámetros incompletos.');
  http_response_code(400);
  exit('Parámetros incompletos');
}

// 1) Verificar merchant
if ($merchantId !== PAYU_MERCHANT_ID) {
  payu_conf_log("merchant_id no coincide: $merchantId");
  http_response_code(401);
  exit('Merchant inválido');
}

// 2) Verificar firma (evita notificaciones falsas)
$expected = payu_signature($referenceSale, $value, $currency);
if (!hash_equals($expected, $signature)) {
  payu_conf_log("Firma inválida para ref $referenceSale");
  http_response_code(401);
  exit('Firma inválida');
}

// 3) En producción ignorar notificaciones de test
if (PAYU_ENV === 'live' && $test === '1') {
  payu_conf_log("Notificación test ignorada en producción (ref $referenceSale)");
  exit('OK');
}

// 4) Buscar la orden por su referencia
try {
  ensure_orders_schema($conexion);
  $stmt = $conexion->prepare("SELECT id, status, user_email, total, pay_method FROM orders_pg WHERE reference_code = ? LIMIT 1");
  $stmt->execute([$referenceSale]);
  $order = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt->closeCursor();
} catch (Throwable $e) {
  payu_conf_log('DB lookup error: ' . $e->getMessage());
  http_response_code(500);
  exit('Error DB');
}

if (!$order) {
  payu_conf_log("Orden no encontrada para ref $referenceSale");
  http_response_code(404);
  exit('Orden no encontrada');
}

$orderId = intval($order['id']);
$newStatus = payu_state_to_status($statePol);

// 5) Idempotencia: si ya está COMPLETED y la notificación vuelve a ser aprobada, no re-procesar
if ($order['status'] === 'COMPLETED' && $newStatus === 'COMPLETED') {
  payu_conf_log("Ref $referenceSale ya procesada como aprobada. Nada que hacer.");
  echo 'OK';
  exit;
}

try {
  // 6) Actualizar el pedido en la BD (solo si el estado realmente cambió)
  $wasPendingOrNew = ($order['status'] !== $newStatus);
  if ($wasPendingOrNew) {
    $isApproved = ($newStatus === 'COMPLETED');
    $stmtUpd = $conexion->prepare("
      UPDATE orders_pg
      SET status = ?, transaction_id = ?, payment_confirmed = ?, payment_confirmed_at = ?
      WHERE id = ?
    ");
    $stmtUpd->execute([
      $newStatus,
      $transactionId !== '' ? $transactionId : null,
      $isApproved ? true : false,
      $isApproved ? date('Y-m-d H:i:s') : null,
      $orderId
    ]);
    $stmtUpd->closeCursor();

    cache_respuesta_invalidar();

    // Notificaciones solo cuando queda APROBADO (primera vez)
    if ($isApproved) {
      try {
        ensure_notificaciones_schema($conexion);
        record_notificacion(
          $conexion,
          'sale',
          "Pago aprobado — Venta #$orderId",
          "Pago PayU aprobado por $" . number_format(floatval($order['total']), 0) . " (ref $referenceSale, trans $transactionId)",
          $orderId,
          $referenceSale
        );
        cache_respuesta_invalidar();
      } catch (Throwable $_) {}

      try {
        dc_notify_new_order($orderId, (string)$order['user_email'], (float)$order['total'], 'payu');
      } catch (Throwable $_) {}
    } else {
      payu_conf_log("Ref $referenceSale -> estado " . $newStatus . " (state_pol $statePol" . ($responseMessage !== '' ? ": $responseMessage" : '') . ")");
    }
  }
} catch (Throwable $e) {
  payu_conf_log('Error actualizando orden: ' . $e->getMessage());
  http_response_code(500);
  exit('Error actualizando orden');
}

echo 'OK';
?>
