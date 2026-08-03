<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/../core/admin_auth.php';
require_once __DIR__ . '/../core/conexion.php';

// Crear tablas si no existen (solo la primera vez; evita DDL costoso en cada petición)
function ensure_orders_tables(PDO $db): void {
  static $done = false;
  if ($done) return;
  try {
    $st = $db->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'orders_pg' LIMIT 1");
    $exists = (bool)$st->fetch();
    $st->closeCursor();
  } catch (Throwable $e) {
    $exists = false;
  }
  if (!$exists) {
    $db->exec("CREATE TABLE IF NOT EXISTS orders_pg (
      id SERIAL PRIMARY KEY,
      paypal_id VARCHAR(64) NULL,
      user_email VARCHAR(255) NULL,
      user_name VARCHAR(255) NULL,
      status VARCHAR(32) NOT NULL,
      total DECIMAL(12,2) NOT NULL DEFAULT 0,
      delivery_method VARCHAR(32) NOT NULL,
      address_json TEXT NULL,
      schedule_json TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS order_items_pg (
      id SERIAL PRIMARY KEY,
      order_id INT NOT NULL,
      title VARCHAR(255) NULL,
      price DECIMAL(12,2) NOT NULL DEFAULT 0,
      qty INT NOT NULL DEFAULT 1,
      image TEXT NULL,
      FOREIGN KEY (order_id) REFERENCES orders_pg(id) ON DELETE CASCADE
    )");

    // Índices para acelerar consultas frecuentes del panel (listados, ventas, notificaciones)
    $db->exec("CREATE INDEX IF NOT EXISTS idx_orders_pg_status ON orders_pg(status)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_orders_pg_created_at ON orders_pg(created_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_order_items_pg_order_id ON order_items_pg(order_id)");
    try {
      $db->exec("CREATE INDEX IF NOT EXISTS idx_facturas_fecha ON facturas(fecha_emision DESC)");
    } catch (Throwable $e) { /* la tabla facturas puede no existir aún */ }
  }
  $done = true;
}

ensure_orders_tables($conexion);

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

$query = "SELECT id, paypal_id, user_email, user_name, status, total, delivery_method, address_json, schedule_json, created_at FROM orders_pg";
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