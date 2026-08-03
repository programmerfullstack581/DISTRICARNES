<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);

try {
    require_once __DIR__ . '/../core/conexion.php'; // PDO

    // Capturar cualquier salida accidental de conexion.php o security.php
    $accidentalOutput = ob_get_contents();
    if (!empty($accidentalOutput)) {
        ob_clean();
    }

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    $email = isset($_GET['email']) ? trim($_GET['email']) : null;
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    if(!$email && is_array($input)){ $email = isset($input['email']) ? trim($input['email']) : null; }
    if(!$userId && is_array($input)){ $userId = isset($input['user_id']) ? intval($input['user_id']) : 0; }

    // Fallback a sesión si no hay email ni id en la petición
    if (!$email && !$userId) {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $email = $_SESSION['user_email'] ?? null;
        $userId = $_SESSION['user_id'] ?? 0;
    }

    if(!$email && !$userId){ 
        ob_end_clean();
        echo json_encode(['ok'=>true,'orders'=>[],'note'=>'No email or user_id provided']); 
        exit; 
    }

    // Asegurar tablas (solo si es necesario, pero lo hacemos rápido)
    try {
        $conexion->exec("CREATE TABLE IF NOT EXISTS orders_pg (
          id SERIAL PRIMARY KEY,
          user_id INT NULL,
          paypal_id VARCHAR(255) NULL,
          user_email VARCHAR(255) NULL,
          user_name VARCHAR(255) NULL,
          status VARCHAR(32) NOT NULL,
          total NUMERIC(12,2) NOT NULL DEFAULT 0,
          delivery_method VARCHAR(32) NOT NULL,
          pay_method VARCHAR(32) NULL,
          address_json JSONB NULL,
          schedule_json JSONB NULL,
          factus_invoice_id VARCHAR(255) NULL,
          factus_number VARCHAR(255) NULL,
          factus_status VARCHAR(32) NULL,
          factus_pdf_url TEXT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        try { $conexion->exec("ALTER TABLE orders_pg ADD COLUMN IF NOT EXISTS user_id INT NULL"); } catch(Throwable $_){}
    } catch (Throwable $_) {}

    $rows = [];
    
    // 1. orders_pg (Búsqueda por Email, Nombre o UserID)
    try {
        $whereParts = [];
        $params = [];
        if ($email) {
            $whereParts[] = "(TRIM(LOWER(user_email)) = TRIM(LOWER(?)) OR TRIM(LOWER(user_name)) = TRIM(LOWER(?)))";
            $params[] = $email; $params[] = $email;
        }
        if ($userId > 0) {
            $whereParts[] = "user_id = ?";
            $params[] = $userId;
        }
        
        if (!empty($whereParts)) {
            $where = implode(" OR ", $whereParts);
            $stmt = $conexion->prepare("SELECT id, user_id, paypal_id, user_email, user_name, status, total, delivery_method, pay_method, address_json, schedule_json, created_at FROM orders_pg WHERE $where ORDER BY created_at DESC");
            $stmt->execute($params);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($res) $rows = array_merge($rows, $res);
        }
    } catch (Throwable $_) {}

    // 2. orders (antigua)
    try {
        if ($email) {
            $stmt = $conexion->prepare("SELECT id, NULL as user_id, paypal_id, user_email, user_name, status, total, delivery_method, NULL as pay_method, address_json, schedule_json, created_at FROM orders WHERE (TRIM(LOWER(user_email)) = TRIM(LOWER(?)) OR TRIM(LOWER(user_name)) = TRIM(LOWER(?))) ORDER BY created_at DESC");
            $stmt->execute([$email, $email]);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($res) $rows = array_merge($rows, $res);
        }
    } catch (Throwable $_) {}

    // 3. venta
    try {
        $whereParts = [];
        $params = [];
        if ($email) {
            $whereParts[] = "(TRIM(LOWER(u.correo_electronico)) = TRIM(LOWER(?)) OR TRIM(LOWER(u.nombres_completos)) = TRIM(LOWER(?)))";
            $params[] = $email; $params[] = $email;
        }
        if ($userId > 0) {
            $whereParts[] = "u.id_usuario = ?";
            $params[] = $userId;
        }
        
        if (!empty($whereParts)) {
            $where = implode(" OR ", $whereParts);
            $stmt = $conexion->prepare("
              SELECT v.id_venta as id, u.id_usuario as user_id, NULL as paypal_id, u.correo_electronico as user_email, u.nombres_completos as user_name, 
                     'COMPLETED' as status, v.total, 'domicilio' as delivery_method, v.metodo_pago as pay_method, 
                     NULL as address_json, NULL as schedule_json, v.fecha as created_at 
              FROM venta v
              JOIN usuario u ON v.id_usuario = u.id_usuario
              WHERE $where
              ORDER BY v.fecha DESC
            ");
            $stmt->execute($params);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($res) $rows = array_merge($rows, $res);
        }
    } catch (Throwable $_) {}

    // Eliminar duplicados si los hay (por ID)
    $uniqueOrders = [];
    foreach ($rows as $r) {
        $key = $r['id'] . '_' . ($r['user_id'] ?? '0');
        if (!isset($uniqueOrders[$key])) {
            $uniqueOrders[$key] = $r;
        }
    }
    $rows = array_values($uniqueOrders);

    // Ordenar
    if (count($rows) > 0) {
        usort($rows, function($a, $b) {
            $t1 = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $t2 = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
            return $t2 - $t1;
        });
    }

    $orders = [];
    foreach($rows as $row){
        $row['address'] = !empty($row['address_json']) ? (json_decode($row['address_json'], true) ?: null) : null;
        $row['schedule'] = !empty($row['schedule_json']) ? (json_decode($row['schedule_json'], true) ?: null) : null;
        unset($row['address_json'], $row['schedule_json']);
        
        $rowItems = [];
        $oid = $row['id'];
        try {
            // Intentar cada tabla de items
            $st = $conexion->prepare("SELECT title, price, qty, image FROM order_items_pg WHERE order_id = ?");
            $st->execute([$oid]);
            $rowItems = $st->fetchAll(PDO::FETCH_ASSOC);
            
            if (!$rowItems) {
                $st = $conexion->prepare("SELECT title, price, qty, image FROM order_items WHERE order_id = ?");
                $st->execute([$oid]);
                $rowItems = $st->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if (!$rowItems) {
                // De detalle_venta (ajustado a nombres de columnas reales)
                $st = $conexion->prepare("SELECT p.nombre as title, dv.precio_unitario as price, dv.cantidad as qty, p.imagen_producto as image FROM detalle_venta dv JOIN producto p ON dv.id_producto = p.id_producto WHERE dv.id_venta = ?");
                $st->execute([$oid]);
                $rowItems = $st->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $_) {}
        
        $row['items'] = $rowItems ?: [];
        $orders[] = $row;
    }

    ob_end_clean();
    echo json_encode(['ok' => true, 'orders' => $orders, 'count' => count($orders)]);

} catch (Throwable $e) {
    if (ob_get_level() > 0) ob_end_clean();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
 
