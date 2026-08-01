<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/conexion.php'; // PDO PostgreSQL

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

$status   = 'PENDING';
$total    = isset($input['total']) ? floatval($input['total']) : 0.0;
$delivery = $input['delivery'] ?? 'domicilio';
$address  = $input['address'] ?? [];
$schedule = $input['schedule'] ?? [];
$items    = $input['items'] ?? [];
$user     = $input['user'] ?? [];
$pay      = $input['pay'] ?? null;

// Recalcular total para asegurar envío gratis si > 10,000
$calculatedSubtotal = 0;
if (is_array($items)) {
  foreach ($items as $it) {
    $p = floatval($it['price'] ?? 0);
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

  $conexion->exec("
    CREATE TABLE IF NOT EXISTS orders_pg (
      id SERIAL PRIMARY KEY,
      user_id INT NULL,
      user_email VARCHAR(255),
      user_name VARCHAR(255),
      status VARCHAR(32) NOT NULL,
      total NUMERIC(12,2) NOT NULL DEFAULT 0,
      delivery_method VARCHAR(32) NOT NULL,
      pay_method VARCHAR(32) NULL,
      address_json JSONB NULL,
      schedule_json JSONB NULL,
      paypal_id VARCHAR(255) NULL,
      factus_invoice_id VARCHAR(255) NULL,
      factus_number VARCHAR(255) NULL,
      factus_status VARCHAR(32) NULL,
      factus_pdf_url TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  ");
  // Asegurar columnas para evitar errores si la tabla ya existe
  try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS user_id INT NULL"); } catch(Throwable $_){}
  try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS paypal_id VARCHAR(255) NULL"); } catch(Throwable $_){}
  try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS factus_invoice_id VARCHAR(255) NULL"); } catch(Throwable $_){}
  try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS factus_number VARCHAR(255) NULL"); } catch(Throwable $_){}
  try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS factus_status VARCHAR(32) NULL"); } catch(Throwable $_){}
  try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS factus_pdf_url TEXT NULL"); } catch(Throwable $_){}

  $conexion->exec("
    CREATE TABLE IF NOT EXISTS order_items_pg (
      id SERIAL PRIMARY KEY,
      order_id INT NOT NULL REFERENCES orders_pg(id) ON DELETE CASCADE,
      title VARCHAR(255),
      price NUMERIC(12,2) NOT NULL DEFAULT 0,
      qty INT NOT NULL DEFAULT 1,
      image TEXT NULL
    )
  ");
  $stmt = $conexion->prepare('
    INSERT INTO orders_pg (user_id, user_email, user_name, status, total, delivery_method, pay_method, address_json, schedule_json)
    VALUES (?,?,?,?,?,?,?,?::jsonb,?::jsonb)
    RETURNING id
  ');
  $stmt->execute([$userId ?: null, $userEmail, $userName, $status, $total, $delivery, $pay, json_encode($address), json_encode($schedule)]);
  $orderId = intval($stmt->fetchColumn());

  if (is_array($items)) {
    $ins = $conexion->prepare('INSERT INTO order_items_pg (order_id, title, price, qty, image) VALUES (?,?,?,?,?)');
    foreach ($items as $it) {
      $title = $it['title'] ?? ($it['name'] ?? 'Producto');
      $price = floatval($it['price'] ?? 0);
      $qty   = intval($it['qty'] ?? ($it['quantity'] ?? 1));
      $img   = $it['image'] ?? ($it['img'] ?? null);
      $ins->execute([$orderId, $title, $price, $qty, $img]);
      
      // Disminuir el stock del producto
      $productId = isset($it['id']) ? intval($it['id']) : 0;
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
  echo json_encode(['ok'=>true,'order_id'=>$orderId]);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
?> 
