<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
  exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($id <= 0 || $email === '') {
  echo json_encode(['success' => false, 'message' => 'Parámetros incompletos.']);
  exit;
}

try {
  $stmt = $conexion->prepare('SELECT id_usuario, nombres_completos, correo_electronico, rol FROM usuario WHERE id_usuario = ? AND correo_electronico = ? LIMIT 1');
  $stmt->execute([$id, $email]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($u) {
    $_SESSION['user_id'] = $u['id_usuario'];
    $_SESSION['user_email'] = $u['correo_electronico'];
    $_SESSION['user_name']  = $u['nombres_completos'];
    $_SESSION['rol']        = $u['rol'];
    $_SESSION['logged_in']  = true;
    echo json_encode(['success' => true, 'message' => 'Sesión restaurada.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Usuario no coincide.']);
  }
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error al restaurar sesión.']);
}
?>
