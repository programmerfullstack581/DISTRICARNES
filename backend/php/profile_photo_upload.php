<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
  require_once __DIR__ . '/conexion.php';

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
  }

  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
  }

  if (!isset($_FILES['photo']) || !is_uploaded_file($_FILES['photo']['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen']); exit;
  }

  $file = $_FILES['photo'];
  $maxSize = 2 * 1024 * 1024; // 2MB
  $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

  $mime = mime_content_type($file['tmp_name']);
  if (!isset($allowedMime[$mime])) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido. Usa JPG/PNG/WEBP']); exit;
  }
  if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'La imagen supera 2MB']); exit;
  }

  $ext = $allowedMime[$mime];
  $userId = (int)$_SESSION['user_id'];
  $baseDir = dirname(__DIR__, 2); // raíz del proyecto
  $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_photos';
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
      echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio de subida']); exit;
    }
  }

  $safeName = 'u' . $userId . '_' . bin2hex(random_bytes(6)) . '_' . time() . '.' . $ext;
  $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

  if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']); exit;
  }

  // Ruta pública relativa
  $publicUrl = '/uploads/profile_photos/' . $safeName;

  // Guardar en BD
  $stmt = $conexion->prepare("UPDATE usuario SET usuario_foto = ? WHERE id_usuario = ?");
  if (!$stmt->execute([$publicUrl, $userId])) {
    @unlink($destPath);
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil']); exit;
  }

  echo json_encode(['success' => true, 'message' => 'Foto actualizada', 'url' => $publicUrl]);
  exit;
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error del servidor', 'detail' => $e->getMessage()]);
  exit;
}
?>
