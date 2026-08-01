<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/conexion.php';     // PDO
require_once __DIR__ . '/mail_sender.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$orderId = isset($input['order_id']) ? intval($input['order_id']) : 0;
$toEmail = isset($input['to']) ? trim($input['to']) : null;
if($orderId <= 0){ echo json_encode(['ok'=>false,'error'=>'order_id is required']); exit; }

try {
  // Cargar orden desde orders_pg primero; fallback a orders
  $stmt = $conexion->prepare('SELECT id, paypal_id, user_email, user_name, status, total, delivery_method, pay_method, address_json, schedule_json, created_at, factus_invoice_id, factus_number, factus_status, factus_pdf_url FROM orders_pg WHERE id = ?');
  $stmt->execute([$orderId]);
  $order = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$order) {
    $stmt = $conexion->prepare('SELECT id, paypal_id, user_email, user_name, status, total, delivery_method, NULL as pay_method, address_json, schedule_json, created_at, factus_invoice_id, factus_number, factus_status, factus_pdf_url FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  // ÚLTIMO FALLBACK: Tabla 'venta' del sistema original
  if (!$order) {
    try {
      $stmt = $conexion->prepare("
        SELECT v.id_venta as id, NULL as paypal_id, u.correo_electronico as user_email, u.nombres_completos as user_name, 
               'COMPLETED' as status, v.total, 'domicilio' as delivery_method, v.metodo_pago as pay_method, 
               NULL as address_json, NULL as schedule_json, v.fecha as created_at,
               NULL as factus_invoice_id, NULL as factus_number, NULL as factus_status, NULL as factus_pdf_url
        FROM venta v
        JOIN usuario u ON v.id_usuario = u.id_usuario
        WHERE v.id_venta = ?
      ");
      $stmt->execute([$orderId]);
      $order = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { /* no existe tabla venta */ }
  }

  if(!$order){ echo json_encode(['ok'=>false,'error'=>'Order not found']); exit; }
  if(!$toEmail){ $toEmail = $order['user_email']; }
  if(!$toEmail){ echo json_encode(['ok'=>false,'error'=>'Recipient email is required']); exit; }

  // Items

  // Items
  $stI = $conexion->prepare('SELECT title, price, qty FROM order_items_pg WHERE order_id = ?');
  $stI->execute([$orderId]);
  $items = $stI->fetchAll(PDO::FETCH_ASSOC);
  if (!$items || !count($items)) {
    $stI = $conexion->prepare('SELECT title, price, qty FROM order_items WHERE order_id = ?');
    $stI->execute([$orderId]);
    $items = $stI->fetchAll(PDO::FETCH_ASSOC);
  }
  
  // Items desde detalle_venta (mapeando campos)
  if (!$items || !count($items)) {
    try {
      $stI = $conexion->prepare('
        SELECT p.nombre as title, dv.precio_unitario as price, dv.cantidad as qty 
        FROM detalle_venta dv
        JOIN producto p ON dv.id_producto = p.id_producto
        WHERE dv.id_venta = ?
      ');
      $stI->execute([$orderId]);
      $items = $stI->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { /* no existe detalle_venta */ }
  }

  $address = !empty($order['address_json']) ? (json_decode($order['address_json'], true) ?: []) : [];

  // Empresa
  $companyName = 'DistriCarnes Hermanos Navarro';
  $companyEmail = MAIL_FROM;
  $companyPhone = '+57 301 5210177';
  $companyAddress = 'OLAYA HERRERA, Cartagena de Indias';
  $currency = 'COP';

  // Usar URL directa para el logo en lugar de base64 para evitar recortes de Gmail
  $logoUrl = 'https://districarnes-83qm.onrender.com/assets/icon/LOGO-DISTRICARNES.png';

  $itemsHtml = '';
  $subtotal = 0.0;
  foreach($items as $it){
    $line = floatval($it['price']) * intval($it['qty']);
    $subtotal += $line;
    $itemsHtml .= '<tr>'
      . '<td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($it['title'] ?: 'Producto') . '</td>'
      . '<td style="padding:8px;border-bottom:1px solid #eee;text-align:center;">' . intval($it['qty']) . '</td>'
      . '<td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">$' . number_format($line, 0, ',', '.') . '</td>'
      . '</tr>';
  }
  $IVA_RATE = 0.19;
  $base = $subtotal / (1 + $IVA_RATE);
  $tax = max(0, $subtotal - $base);
  
  // Lógica de envío: Gratis si subtotal >= 10,000
  $FREE_SHIPPING_THRESHOLD = 10000;
  $shipping = ($subtotal >= $FREE_SHIPPING_THRESHOLD || ($order['delivery_method'] ?? '') === 'punto') ? 0 : 7000;
  
  $total = $subtotal + $shipping;

  $createdAt = !empty($order['created_at']) ? strtotime($order['created_at']) : time();
  $invoiceCode = 'FAC-' . date('Ymd', $createdAt) . '-' . strtoupper(base_convert($orderId, 10, 36));

  // HTML optimizado para peso mínimo (Evita recorte de Gmail)
  $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
    . 'body{font-family:sans-serif;color:#333;margin:0;padding:10px;line-height:1.2}'
    . '.c{max-width:600px;margin:0 auto;background:#fff;padding:20px;border:1px solid #eee}'
    . '.h{border-bottom:2px solid #f00;padding-bottom:10px;margin-bottom:15px;display:flex;justify-content:space-between;align-items:center}'
    . '.h h1{margin:0;color:#f00;font-size:20px}'
    . 'table{width:100%;border-collapse:collapse;margin:15px 0}'
    . 'th{background:#f9f9f9;padding:8px;text-align:left;border-bottom:1px solid #ddd;font-size:13px}'
    . 'td{padding:8px;font-size:13px}'
    . '.t{width:200px;margin-left:auto}'
    . '.r{display:flex;justify-content:space-between;padding:4px 0;font-size:13px}'
    . '.f{font-weight:bold;color:#f00;font-size:16px;border-top:1px solid #f00;margin-top:10px;padding-top:10px}'
    . '.footer{margin-top:30px;text-align:center;color:#999;font-size:11px}'
    . '.addr{background:#f9f9f9;padding:10px;margin-top:15px;font-size:12px;border-left:3px solid #f00}'
    . '</style></head><body>'
    . '<div class="c">'
      . '<div class="h">'
        . '<div><h1>' . htmlspecialchars($companyName) . '</h1><small>NIT: 900000000-0</small></div>'
        . '<img src="' . $logoUrl . '" height="40" alt="Logo"/>'
      . '</div>'
      . '<div style="display:flex;justify-content:space-between;font-size:13px;">'
        . '<div><b>#' . htmlspecialchars($invoiceCode) . '</b><br>' . date('Y-m-d H:i', $createdAt) . '</div>'
        . '<div style="text-align:right;">' . htmlspecialchars($order['user_name'] ?: 'Cliente') . '<br>' . htmlspecialchars($toEmail) . '</div>'
      . '</div>'
      . '<table><thead><tr><th>Producto</th><th style="text-align:center">Cant.</th><th style="text-align:right">Subtotal</th></tr></thead><tbody>' . $itemsHtml . '</tbody></table>'
      . '<div class="t">'
        . '<div class="r"><span>Base:</span><span>$' . number_format($base, 0, ',', '.') . '</span></div>'
        . '<div class="r"><span>IVA:</span><span>$' . number_format($tax, 0, ',', '.') . '</span></div>'
        . '<div class="r"><span>Envío:</span><span style="color:' . ($shipping > 0 ? '#333' : '#00c853') . '">' . ($shipping > 0 ? ('$' . number_format($shipping, 0, ',', '.')) : 'GRATIS') . '</span></div>'
        . '<div class="r f"><span>TOTAL:</span><span>$' . number_format($total, 0, ',', '.') . '</span></div>'
      . '</div>'
      . '<div style="margin-top:15px;text-align:right;"><a href="https://districarnes-83qm.onrender.com/backend/php/order_invoice.php?order_id=' . $orderId . '&print=1" style="display:inline-block;padding:8px 15px;background:#f00;color:#fff;text-decoration:none;border-radius:4px;font-size:13px;font-weight:bold;">Descargar PDF</a></div>';

  if (!empty($address)) {
    $html .= '<div class="addr"><b>Entrega:</b> ' . htmlspecialchars($address['street'] ?? '') . ', ' . htmlspecialchars($address['city'] ?? '') . (!empty($address['notes']) ? (' (<em>' . htmlspecialchars($address['notes']) . '</em>)') : '') . '</div>';
  }

  $html .= '<div class="footer"><p>Gracias por tu compra en DistriCarnes</p><p>' . htmlspecialchars($companyAddress) . ' • Tel: ' . htmlspecialchars($companyPhone) . '</p></div></div></body></html>';

  $subject = 'Factura de compra ' . $invoiceCode . ' - ' . $companyName;

  try {
    $sendResult = dc_send_mail($toEmail, $subject, $html, 'text/html');
    if (!$sendResult['ok']) {
      $error = $sendResult['error'] ?? 'send failed';
      error_log('[send_invoice_email] Error sending: ' . $error);
      // Para dar feedback amigable en el frontend
      if (strpos($error, 'Contraseña de aplicación') !== false) {
        echo json_encode(['ok'=>false,'error'=>$error,'code'=>'smtp_invalid_app_password']);
      } elseif (strpos($error, 'mail_not_configured') !== false) {
        echo json_encode(['ok'=>false,'error'=>$error,'code'=>'mail_not_configured']);
      } else {
        echo json_encode(['ok'=>false,'error'=>$error]);
      }
      exit;
    }

    // --- GUARDAR EN BASE DE DATOS ---
    try {
      // Asegurar que la tabla facturas exista con todas sus columnas
      $conexion->exec("CREATE TABLE IF NOT EXISTS facturas (
        id_factura SERIAL PRIMARY KEY,
        orden_id INT NOT NULL,
        codigo_factura VARCHAR(64) UNIQUE,
        cliente_nombre VARCHAR(255),
        cliente_email VARCHAR(255),
        subtotal NUMERIC(12,2),
        total NUMERIC(12,2),
        estado VARCHAR(32) DEFAULT 'PENDIENTE',
        fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        metodo_entrega VARCHAR(64),
        metodo_pago VARCHAR(64)
      )");
      
      // Intentar añadir columnas si no existen (migración rápida)
      try { $conexion->exec("ALTER TABLE facturas ADD COLUMN IF NOT EXISTS metodo_pago VARCHAR(64)"); } catch(Throwable $_){}

      // Insertar o actualizar registro de factura
      $stmtFactura = $conexion->prepare("
        INSERT INTO facturas (orden_id, codigo_factura, cliente_nombre, cliente_email, subtotal, total, estado, metodo_entrega, metodo_pago)
        VALUES (?, ?, ?, ?, ?, ?, 'ENVIADA', ?, ?)
        ON CONFLICT (codigo_factura) DO UPDATE SET 
          estado = 'ENVIADA',
          subtotal = EXCLUDED.subtotal,
          total = EXCLUDED.total,
          metodo_pago = EXCLUDED.metodo_pago
      ");
      
      $payMethod = $order['pay_method'] ?? ($order['paypal_id'] ? 'PayPal' : 'Efectivo/Nequi');
      
      $stmtFactura->execute([
        $orderId,
        $invoiceCode,
        $order['user_name'] ?: 'Cliente',
        $toEmail,
        $subtotal,
        $total,
        $order['delivery_method'] ?: 'domicilio',
        $payMethod
      ]);
    } catch (Throwable $dbErr) {
      error_log('[send_invoice_email] Error saving invoice to DB: ' . $dbErr->getMessage());
    }
    // --------------------------------

  } catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    exit;
  }

  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
?> 
