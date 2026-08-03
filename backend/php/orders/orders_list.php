<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/../core/admin_auth.php';
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/orders_schema.php';

// Garantizar esquema completo y consistente de orders_pg / order_items_pg
ensure_orders_schema($conexion);

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

$query = "SELECT id, paypal_id, user_email, user_name, status, total, delivery_method, pay_method, address_json, schedule_json, created_at FROM orders_pg";
$where = [];
$params = [];

if ($status !== '') { $where[] = 'status = ?'; $params[] = $status; }
if ($from !== '') { $where[] = 'created_at >= ?'; $params[] = $from; }
if ($to !== '') { $where[] = 'created_at <= ?'; $params[] = $to; }
if (!empty($where)) { $query .= ' WHERE ' . implode(' AND ', $where); }
$query .= ' ORDER BY created_at DESC';

$stmt = $conexion->prepare($query);
if(!$stmt){ echo json_encode(['ok'=>false,'error'=> implode(' ', $conexion->errorInfo())]); exit; }

$ok = $stmt->execute($params);
if(!$ok){ echo json_encode(['ok'=>false,'error'=> implode(' ', $stmt->errorInfo())]); exit; }

$orders = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
  $addr = $row['address_json'] ? json_decode($row['address_json'], true) : null;
  $sched = $row['schedule_json'] ? json_decode($row['schedule_json'], true) : null;
  unset($row['address_json']);
  unset($row['schedule_json']);

  $orders[] = [
    'id' => intval($row['id']),
    'paypal_id' => $row['paypal_id'],
    'customer_email' => $row['user_email'],
    'customer_name' => $row['user_name'],
    'status' => $row['status'],
    'total' => floatval($row['total']),
    'delivery_method' => $row['delivery_method'],
    'pay_method' => $row['pay_method'],
    'created_at' => $row['created_at'],
    'address' => $addr,
    'schedule' => $sched,
    'items' => [],
    'items_count' => 0
  ];
}
$stmt->closeCursor();

// Cargar todos los items en UNA sola consulta (evita N+1)
$orderIds = array_column($orders, 'id');
$itemsByOrder = [];
if (!empty($orderIds)) {
  $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
  $stmtI = $conexion->prepare("SELECT order_id, title, price, qty, image FROM order_items_pg WHERE order_id IN ($placeholders) ORDER BY order_id, id");
  $stmtI->execute($orderIds);
  while ($it = $stmtI->fetch(PDO::FETCH_ASSOC)) {
    $itemsByOrder[(int)$it['order_id']][] = $it;
  }
  $stmtI->closeCursor();
}
foreach ($orders as &$o) {
  $o['items'] = $itemsByOrder[$o['id']] ?? [];
  $o['items_count'] = count($o['items']);
}
unset($o);

echo json_encode(['ok'=>true, 'orders'=>$orders]);
exit;
?>