<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
  exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.']);
  exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$userId = (int)$_SESSION['user_id'];

switch ($action) {
  case 'get_profile':
    getProfile($conexion, $userId);
    break;
  case 'update_profile':
    updateProfile($conexion, $userId);
    break;
  case 'change_password':
    changePassword($conexion, $userId);
    break;
  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}

function getProfile(PDO $conexion, int $userId) {
  try {
    $sql = "SELECT id_usuario, nombres_completos, correo_electronico, rol FROM usuario WHERE id_usuario = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$userId]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
      echo json_encode([
        'success' => true,
        'user' => [
          'id' => $u['id_usuario'],
          'nombres_completos' => $u['nombres_completos'],
          'correo_electronico' => $u['correo_electronico'],
          'rol' => $u['rol']
        ]
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }
  } catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener perfil.']);
  }
}

function updateProfile(PDO $conexion, int $userId) {
  $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';

  if ($fullName === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
    return;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido.']);
    return;
  }
  try {
    $sql_check = "SELECT id_usuario FROM usuario WHERE correo_electronico = ? AND id_usuario != ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$email, $userId]);
    $exists = $stmt_check->fetch(PDO::FETCH_ASSOC);
    if ($exists) {
      echo json_encode(['success' => false, 'message' => 'El correo ya está en uso por otro usuario.']);
      return;
    }

    $sql = "UPDATE usuario SET nombres_completos = ?, correo_electronico = ? WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([$fullName, $email, $userId]);
    if ($ok) {
      $_SESSION['user_name'] = $fullName;
      $_SESSION['user_email'] = $email;
      echo json_encode(['success' => true, 'message' => 'Perfil actualizado.']);
    } else {
      echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil.']);
    }
  } catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil.']);
  }
}

function changePassword(PDO $conexion, int $userId) {
  $currentPassword = isset($_POST['currentPassword']) ? $_POST['currentPassword'] : '';
  $newPassword = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';
  $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

  if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
    return;
  }
  if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
    return;
  }
  if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden.']);
    return;
  }
  try {
    $sql_get = "SELECT contrasena FROM usuario WHERE id_usuario = ?";
    $stmt_get = $conexion->prepare($sql_get);
    $stmt_get->execute([$userId]);
    $user = $stmt_get->fetch(PDO::FETCH_ASSOC);
    if ($user) {
      if (!password_verify($currentPassword, $user['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
        return;
      }
      $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
      $sql_upd = "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?";
      $stmt_upd = $conexion->prepare($sql_upd);
      $ok = $stmt_upd->execute([$newHash, $userId]);
      if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada.']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }
  } catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cambiar la contraseña.']);
  }
}

// En PDO no es necesario cerrar explícitamente
?>
