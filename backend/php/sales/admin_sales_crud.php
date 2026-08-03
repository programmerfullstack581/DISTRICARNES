<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/admin_auth.php';
require_once __DIR__ . '/../core/conexion.php'; // PDO
require_once __DIR__ . '/../core/cache_respuesta.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'list');

// Asegurar tablas
try {
  $conexion->exec("CREATE TABLE IF NOT EXISTS orders_pg (
    id SERIAL PRIMARY KEY,
    paypal_id VARCHAR(64) NULL,
    user_email VARCHAR(255) NULL,
    user_name VARCHAR(255) NULL,
    status VARCHAR(32) NOT NULL,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    delivery_method VARCHAR(32) NOT NULL DEFAULT 'domicilio',
    pay_method VARCHAR(32) NULL,
    address_json JSONB NULL,
    schedule_json JSONB NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
  $conexion->exec("CREATE TABLE IF NOT EXISTS order_items_pg (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders_pg(id) ON DELETE CASCADE,
    title VARCHAR(255),
    price NUMERIC(12,2) NOT NULL DEFAULT 0,
    qty INT NOT NULL DEFAULT 1,
    image TEXT NULL
  )");
} catch (Throwable $e) { error_log('admin_sales_crud.php: ' . $e->getMessage()); echo json_encode(['ok'=>false,'error'=>'Error de base de datos']); exit; }

switch ($action) {
  case 'list':
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $from   = isset($_GET['from']) ? trim($_GET['from']) : '';
    $to     = isset($_GET['to']) ? trim($_GET['to']) : '';
    $q = "SELECT id, paypal_id, user_email, user_name, status, total, pay_method, created_at FROM orders_pg";
    $where = [];
    $p = [];
    if ($status !== '') { $where[] = 'status = ?'; $p[] = $status; }
    if ($from !== '') { $where[] = 'created_at >= ?'; $p[] = $from; }
    if ($to !== '') { $where[] = 'created_at <= ?'; $p[] = $to; }
    if (!empty($where)) { $q .= ' WHERE ' . implode(' AND ', $where); }
    $q .= ' ORDER BY created_at DESC';
    $st = $conexion->prepare($q);
    if (!$st || !$st->execute($p)) { echo json_encode(['ok'=>false,'error'=>'query_error']); exit; }
    $rows = [];
    while($r = $st->fetch(PDO::FETCH_ASSOC)){
      $rows[] = [
        'id'=>intval($r['id']),
        'paypal_id'=>$r['paypal_id'],
        'email'=>$r['user_email'],
        'name'=>$r['user_name'],
        'status'=>$r['status'],
        'total'=>floatval($r['total']),
        'pay_method'=>$r['pay_method'],
        'created_at'=>$r['created_at']
      ];
    }
    echo json_encode(['ok'=>true,'sales'=>$rows]);
    exit;

  case 'create':
    $raw = file_get_contents('php://input');
    $in = json_decode($raw, true);
    if (!is_array($in)) { echo json_encode(['ok'=>false,'error'=>'invalid_json']); exit; }
    $email = trim($in['email'] ?? '');
    $name  = trim($in['name'] ?? '');
    $total = floatval($in['total'] ?? 0);
    $status = strtoupper(trim($in['status'] ?? 'COMPLETED'));
    $pay = $in['pay_method'] ?? 'manual';
    $items = is_array($in['items'] ?? null) ? $in['items'] : [];
    $st = $conexion->prepare("INSERT INTO orders_pg (user_email,user_name,status,total,delivery_method,pay_method,address_json,schedule_json) VALUES (?,?,?,?, 'domicilio', ?, NULL, NULL) RETURNING id");
    $st->execute([$email,$name,$status,$total,$pay]);
    $orderId = intval($st->fetchColumn());
    if ($items) {
      $ins = $conexion->prepare("INSERT INTO order_items_pg (order_id,title,price,qty,image) VALUES (?,?,?,?,?)");
      foreach($items as $it){
        $title = $it['title'] ?? 'Producto';
        $price = floatval($it['price'] ?? 0);
        $qty   = intval($it['qty'] ?? 1);
        $img   = $it['image'] ?? null;
        $ins->execute([$orderId,$title,$price,$qty,$img]);
      }
    }
    cache_respuesta_invalidar();
    echo json_encode(['ok'=>true,'id'=>$orderId]);
    exit;

  case 'update':
    $raw = file_get_contents('php://input');
    $in = json_decode($raw, true);
    $id = intval($in['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'id_required']); exit; }
    $fields = [];
    $params = [];
    if (isset($in['email'])) { $fields[] = 'user_email = ?'; $params[] = trim($in['email']); }
    if (isset($in['name'])) { $fields[] = 'user_name = ?'; $params[] = trim($in['name']); }
    if (isset($in['status'])) { $fields[] = 'status = ?'; $params[] = strtoupper(trim($in['status'])); }
    if (isset($in['total'])) { $fields[] = 'total = ?'; $params[] = floatval($in['total']); }
    if (isset($in['pay_method'])) { $fields[] = 'pay_method = ?'; $params[] = trim($in['pay_method']); }
    if (empty($fields)) { echo json_encode(['ok'=>false,'error'=>'nothing_to_update']); exit; }
    $params[] = $id;
    $sql = "UPDATE orders_pg SET " . implode(', ', $fields) . " WHERE id = ?";
    $ok = $conexion->prepare($sql)->execute($params);
    if ($ok) { cache_respuesta_invalidar(); }
    echo json_encode(['ok'=>$ok]);
    exit;

  case 'delete':
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'id_required']); exit; }
    $ok = $conexion->prepare("DELETE FROM orders_pg WHERE id = ?")->execute([$id]);
    if ($ok) { cache_respuesta_invalidar(); }
    echo json_encode(['ok'=>$ok]);
    exit;
}

echo json_encode(['ok'=>false,'error'=>'unknown_action']);
exit;
?> 
