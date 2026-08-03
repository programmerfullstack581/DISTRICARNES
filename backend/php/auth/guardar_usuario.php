<?php
require_once __DIR__ . '/../core/conexion.php'; // $conexion es un PDO (PostgreSQL)
require_once __DIR__ . '/../core/mail_sender.php';

function dc_ensure_user_verification_cols(PDO $db): void {
  try { $db->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified BOOLEAN NOT NULL DEFAULT FALSE"); } catch (Throwable $_) {}
  try { $db->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL"); } catch (Throwable $_) {}
  try { $db->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_token_hash VARCHAR(64) NULL"); } catch (Throwable $_) {}
  try { $db->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_expires TIMESTAMP NULL"); } catch (Throwable $_) {}
}

function dc_base_url(): string {
  $host = $_SERVER['HTTP_HOST'] ?? '';
  if ($host === '') return '';
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  $scheme = $https ? 'https' : 'http';
  return $scheme . '://' . $host;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['verify'], $_GET['email'], $_GET['token'])) {
  $email = trim((string)$_GET['email']);
  $token = trim((string)$_GET['token']);

  $ok = false;
  $reason = 'invalid';

  if (filter_var($email, FILTER_VALIDATE_EMAIL) && $token !== '') {
    try {
      dc_ensure_user_verification_cols($conexion);
      $stmt = $conexion->prepare("SELECT id_usuario, email_verified, email_verify_token_hash, email_verify_expires FROM usuario WHERE correo_electronico = ? LIMIT 1");
      $stmt->execute([$email]);
      $u = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt->closeCursor();

      if ($u && ($u['email_verified'] ?? false)) {
        $ok = true;
        $reason = 'already_verified';
      } elseif ($u) {
        $hash = hash('sha256', $token);
        $dbHash = (string)($u['email_verify_token_hash'] ?? '');
        $expires = $u['email_verify_expires'] ?? null;
        $notExpired = true;
        if ($expires) {
          $notExpired = (strtotime((string)$expires) >= time());
        }
        if ($dbHash !== '' && hash_equals($dbHash, $hash) && $notExpired) {
          $stmt = $conexion->prepare("UPDATE usuario SET email_verified = TRUE, email_verified_at = CURRENT_TIMESTAMP, email_verify_token_hash = NULL, email_verify_expires = NULL, updated_at = CURRENT_TIMESTAMP WHERE id_usuario = ?");
          $stmt->execute([$u['id_usuario']]);
          $stmt->closeCursor();
          $ok = true;
          $reason = 'verified';
        } else {
          $reason = $notExpired ? 'token_invalid' : 'token_expired';
        }
      } else {
        $reason = 'not_found';
      }
    } catch (Throwable $_) {
      $ok = false;
      $reason = 'server';
    }
  } else {
    $reason = 'invalid_params';
  }

  $base = dc_base_url();
  $redirect = ($base !== '' ? $base : '') . '/login/login.php?verified=' . ($ok ? '1' : '0') . '&reason=' . urlencode($reason);
  header('Location: ' . $redirect, true, 302);
  exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

$nombre    = trim($_POST['nombre'] ?? '');
$cedula    = trim($_POST['cedula'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$celular   = trim($_POST['celular'] ?? '');
$correo    = trim($_POST['email'] ?? '');
$clave     = trim($_POST['contrasena'] ?? '');

if (!$nombre || !$cedula || !$direccion || !$celular || !$correo || !$clave) {
  echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
  exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
  exit;
}

try {
  // Crear tabla si no existe (sintaxis PostgreSQL)
  $conexion->exec("
    CREATE TABLE IF NOT EXISTS usuario (
      id_usuario SERIAL PRIMARY KEY,
      nombres_completos VARCHAR(255) NOT NULL,
      cedula VARCHAR(50) NOT NULL UNIQUE,
      direccion VARCHAR(255) NOT NULL,
      celular VARCHAR(50) NOT NULL,
      correo_electronico VARCHAR(255) NOT NULL UNIQUE,
      contrasena VARCHAR(255) NOT NULL,
      rol VARCHAR(50) NOT NULL DEFAULT 'trabajo',
      email_verified BOOLEAN NOT NULL DEFAULT FALSE,
      email_verified_at TIMESTAMP NULL,
      email_verify_token_hash VARCHAR(64) NULL,
      email_verify_expires TIMESTAMP NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  ");
  dc_ensure_user_verification_cols($conexion);

  // Duplicados por email
  $stmt = $conexion->prepare('SELECT 1 FROM usuario WHERE correo_electronico = ? LIMIT 1');
  $stmt->execute([$correo]);
  if ($stmt->fetchColumn()) {
    echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
    exit;
  }

  // Duplicados por cédula
  $stmt = $conexion->prepare('SELECT 1 FROM usuario WHERE cedula = ? LIMIT 1');
  $stmt->execute([$cedula]);
  if ($stmt->fetchColumn()) {
    echo json_encode(['success' => false, 'message' => 'La cédula ya está registrada']);
    exit;
  }

  // Hashear contraseña
  $hash = password_hash($clave, PASSWORD_BCRYPT);

  $token = bin2hex(random_bytes(32));
  $tokenHash = hash('sha256', $token);
  $expiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

  // Insertar
  $stmt = $conexion->prepare('
    INSERT INTO usuario (nombres_completos, cedula, direccion, celular, correo_electronico, contrasena, rol, email_verified, email_verify_token_hash, email_verify_expires)
    VALUES (?,?,?,?,?,?,?,?,?,?)
  ');
  $rol = 'trabajo';
  $emailVerified = 0;
  $ok = $stmt->execute([$nombre, $cedula, $direccion, $celular, $correo, $hash, $rol, $emailVerified, $tokenHash, $expiresAt]);

  if ($ok) {
    try {
        $base = dc_base_url();
        $verifyUrl = ($base !== '' ? $base : '') . '/backend/php/auth/guardar_usuario.php?verify=1&email=' . urlencode($correo) . '&token=' . urlencode($token);
        $subject = 'Verifica tu correo en DistriCarnes';
        $message = '<div style="font-family:Arial,sans-serif;line-height:1.5;color:#111">' .
          '<h2 style="margin:0 0 12px 0">Hola ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</h2>' .
          '<p style="margin:0 0 12px 0">Para completar tu registro y poder comprar, verifica tu correo haciendo clic en el botón:</p>' .
          '<p style="margin:16px 0"><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:12px 18px;background:#e53e3e;color:#fff;text-decoration:none;border-radius:8px">Verificar mi correo</a></p>' .
          '<p style="margin:0 0 12px 0">Si no puedes abrir el botón, copia y pega este enlace en tu navegador:</p>' .
          '<p style="margin:0 0 12px 0"><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '</a></p>' .
          '<p style="margin:0;color:#555;font-size:12px">Este enlace vence en 24 horas.</p>' .
          '</div>';

        $send = dc_send_mail($correo, $subject, $message, 'text/html');
        if (!$send['ok']) error_log('Error enviando verificación: ' . ($send['error'] ?? 'desconocido'));
    } catch (Throwable $e) {
        error_log('Excepción enviando verificación: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Registro exitoso. Revisa tu correo para verificar la cuenta.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'No se pudo registrar']);
  }
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
