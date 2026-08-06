<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/admin_auth.php';
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/orders_schema.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../sales/sales_utils.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

dc_csrf_require();

$orderId = isset($input['order_id']) ? intval($input['order_id']) : 0;
$status  = isset($input['status']) ? strtolower(trim($input['status'])) : '';

if ($orderId <= 0 || $status === '') {
  echo json_encode(['ok'=>false,'error'=>'order_id and status are required']);
  exit;
}

// Normalizar y validar estado
$map = ['pending'=>'PENDING','processing'=>'PROCESSING','paid'=>'PAID','completed'=>'COMPLETED','cancelled'=>'CANCELLED'];
$statusNorm = $map[$status] ?? strtoupper($status);
if (!in_array($statusNorm, ['PENDING','PROCESSING','PAID','COMPLETED','CANCELLED'], true)) {
  echo json_encode(['ok'=>false,'error'=>'invalid status']);
  exit;
}

// Asegurar esquema antes de cualquier operación
ensure_orders_schema($conexion);

if ($statusNorm === 'PAID') {
  // Confirmar pago: se conserva el estado operativo (PENDING) pero el pago queda confirmado
  $stmt = $conexion->prepare("UPDATE orders_pg SET payment_confirmed = TRUE, payment_confirmed_at = CURRENT_TIMESTAMP, status = ? WHERE id = ?");
  $stmt->execute([$statusNorm, $orderId]);
} else {
  $stmt = $conexion->prepare("UPDATE orders_pg SET status = ? WHERE id = ?");
  $stmt->execute([$statusNorm, $orderId]);
}
$ok = $stmt->rowCount() > 0;

$sale = null;
if ($ok && $statusNorm === 'COMPLETED') {
    // Registrar venta automáticamente cuando la orden queda COMPLETED
    $sale = record_sale_for_order($conexion, $orderId);

    // Registrar notificación de venta completada
    try {
        ensure_notificaciones_schema($conexion);
        record_notificacion(
            $conexion,
            'sale',
            "Orden completada #$orderId",
            "La orden fue marcada como completada",
            $orderId
        );
        cache_respuesta_invalidar();
    } catch (Throwable $_) {}
} else if ($ok) {
    // Registrar notificación de actualización de estado
    try {
        ensure_notificaciones_schema($conexion);
        record_notificacion(
            $conexion,
            'order',
            "Pedido actualizado #$orderId",
            "Estado cambiado a: " . strtolower($statusNorm),
            $orderId
        );
        cache_respuesta_invalidar();
    } catch (Throwable $_) {}
}

echo json_encode(['ok'=>$ok, 'sale'=>$sale]);
exit;
?>