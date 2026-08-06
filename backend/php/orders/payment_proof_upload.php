<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/orders_schema.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/rate_limit.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

dc_csrf_require();

$rl = dc_rate_limit_consume('proof_upload:' . dc_client_ip(), 10, 3600);
if (!$rl['allowed']) {
  http_response_code(429);
  echo json_encode(['ok'=>false, 'error'=>'Has subido demasiados comprobantes. Intenta de nuevo más tarde.', 'code'=>'rate_limited']);
  exit;
}

$orderId = isset($input['order_id']) ? intval($input['order_id']) : 0;
$b64     = isset($input['file_base64']) ? trim((string)$input['file_base64']) : '';
$mime    = isset($input['mime']) ? strtolower(trim((string)$input['mime'])) : 'image/jpeg';

if ($orderId <= 0 || $b64 === '') {
  echo json_encode(['ok'=>false,'error'=>'order_id y file_base64 son requeridos']);
  exit;
}

$allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'application/pdf'=>'pdf'];
if (!isset($allowed[$mime])) {
  echo json_encode(['ok'=>false,'error'=>'Formato no permitido. Usa JPG/PNG/WEBP/PDF']);
  exit;
}

// Quitar prefijo data: si viene incluido
if (strpos($b64, 'base64,') !== false) {
  $b64 = substr($b64, strpos($b64, 'base64,') + 7);
}
$bin = base64_decode($b64, true);
if ($bin === false) {
  echo json_encode(['ok'=>false,'error'=>'Comprobante inválido']);
  exit;
}

$maxSize = 4 * 1024 * 1024; // 4MB
if (strlen($bin) > $maxSize) {
  echo json_encode(['ok'=>false,'error'=>'El comprobante supera 4MB']);
  exit;
}

try {
  ensure_orders_schema($conexion);

  $stmt = $conexion->prepare("SELECT id FROM orders_pg WHERE id = ?");
  $stmt->execute([$orderId]);
  if (!$stmt->fetch()) {
    echo json_encode(['ok'=>false,'error'=>'La orden no existe']);
    exit;
  }
  $stmt->closeCursor();

  $baseDir = dirname(__DIR__, 3); // raíz del proyecto
  $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'payment_proofs';
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
      echo json_encode(['ok'=>false,'error'=>'No se pudo crear el directorio de subida']);
      exit;
    }
  }

  $ext = $allowed[$mime];
  $safeName = 'o' . $orderId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
  $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
  if (file_put_contents($destPath, $bin) === false) {
    echo json_encode(['ok'=>false,'error'=>'Error al guardar el comprobante']);
    exit;
  }

  $publicUrl = '/uploads/payment_proofs/' . $safeName;

  $upd = $conexion->prepare("UPDATE orders_pg SET payment_proof = ? WHERE id = ?");
  if (!$upd->execute([$publicUrl, $orderId])) {
    @unlink($destPath);
    echo json_encode(['ok'=>false,'error'=>'No se pudo guardar el comprobante en la orden']);
    exit;
  }

  echo json_encode(['ok'=>true, 'url'=>$publicUrl]);
  exit;
} catch (Throwable $e) {
  error_log('payment_proof_upload.php: ' . $e->getMessage());
  echo json_encode(['ok'=>false,'error'=>'Error del servidor al subir el comprobante']);
  exit;
}
?>
