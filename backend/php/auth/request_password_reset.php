<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/email_config.php';
require_once __DIR__ . '/../core/smtp_mailer.php';
require_once __DIR__ . '/../core/rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

// Solo en modo depuración se devuelve el enlace en la respuesta (evita secuestro de cuenta)
$debugMode = getenv('DC_DEBUG') === '1';

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
  exit;
}

// =========================================
// RATE LIMIT - Evitar inundación de correos / fuerza bruta
// =========================================
$rlEmailKey = 'reset:email:' . strtolower($email);
$rlIpKey    = 'reset:ip:' . dc_client_ip();

$peekEmail = dc_rate_limit_peek($rlEmailKey, 3, 900);
$peekIp    = dc_rate_limit_peek($rlIpKey, 5, 900);
if (!$peekEmail['allowed'] || !$peekIp['allowed']) {
  echo json_encode(['success' => false, 'message' => 'Has solicitado demasiados enlaces. Intenta de nuevo en unos minutos.']);
  exit;
}
// Consumir antes de validar si el correo existe (evita enumeración por respuesta distinta)
$rlEmail = dc_rate_limit_consume($rlEmailKey, 3, 900);
$rlIp    = dc_rate_limit_consume($rlIpKey, 5, 900);
if (!$rlEmail['allowed'] || !$rlIp['allowed']) {
  echo json_encode(['success' => false, 'message' => 'Has solicitado demasiados enlaces. Intenta de nuevo en unos minutos.']);
  exit;
}

try {
  $stmt = $conexion->prepare('SELECT id_usuario, nombres_completos FROM usuario WHERE correo_electronico = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) {
    echo json_encode(['success' => false, 'message' => 'El correo no está registrado en la base de datos.']);
    exit;
  }

  $conexion->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  // Generar token
  $token = bin2hex(random_bytes(32));
  $tokenHash = hash('sha256', $token);
  $expires = date('Y-m-d H:i:s', time() + 60 * 30); // 30 minutos

  // Guardar token
  $ins = $conexion->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)');
  $ok = $ins->execute([$user['id_usuario'], $tokenHash, $expires]);
  if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'No se pudo generar el enlace.']);
    exit;
  }

  // Construir enlace (usar ruta absoluta válida en producción)
  $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $resetPath = '/login/cambiar_contrasena.php';
  $resetUrl = $scheme . '://' . $host . $resetPath . '?token=' . $token;

  // Intentar enviar correo (requiere configurar SMTP/"mail" en php.ini)
  $subject = 'Recuperación de contraseña - Districarnes';
  $message = "Hola " . ($user['nombres_completos'] ?? '') . ",\n\n" .
             "Recibimos una solicitud para restablecer tu contraseña.\n" .
             "Usa el siguiente enlace para crear una nueva contraseña (válido por 30 minutos):\n" .
             $resetUrl . "\n\n" .
             "Si no solicitaste esto, puedes ignorar este mensaje.";

  // Guardar enlace en log SOLO en modo depuración (no exponer tokens en producción)
  if ($debugMode) {
    try {
      file_put_contents(__DIR__ . '/reset_links.log', date('c') . ' ' . $email . ' => ' . $resetUrl . "\n", FILE_APPEND);
    } catch (Throwable $logErr) {
      // Ignorar errores al escribir log
    }
  }

  // Selección automática de proveedor (prioriza APIs con clave válida)
  $send = ['ok' => false, 'error' => ''];
  $prov = null;
  if (defined('BREVO_API_KEY') && BREVO_API_KEY !== '') {
    $prov = 'http_brevo';
  } elseif (defined('RESEND_API_KEY') && RESEND_API_KEY !== '') {
    $prov = 'http_resend';
  } elseif (defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') {
    $prov = 'http_sendgrid';
  }
  // Si se forzó un proveedor por variable, respetarlo solo si hay clave o si es SMTP
  if (!$prov && defined('MAIL_PROVIDER')) {
    $forced = strtolower(trim(MAIL_PROVIDER));
    if ($forced === 'http_brevo' && defined('BREVO_API_KEY') && BREVO_API_KEY !== '') $prov = 'http_brevo';
    elseif ($forced === 'http_resend' && defined('RESEND_API_KEY') && RESEND_API_KEY !== '') $prov = 'http_resend';
    elseif ($forced === 'http_sendgrid' && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') $prov = 'http_sendgrid';
    elseif ($forced === 'smtp') $prov = 'smtp';
  }
  if (!$prov) $prov = 'smtp';
  try { error_log('[reset] provider=' . $prov); } catch (Throwable $__) {}

  // Solo validar SMTP si realmente se usará SMTP
  if ($prov === 'smtp') {
    $placeholderPasses = ['tu_contrasena_de_aplicacion', 'APP_PASSWORD_AQUI', ''];
    if (SMTP_USER === 'tu_correo@gmail.com' || in_array(SMTP_PASS, $placeholderPasses, true)) {
      $msg = 'Configura SMTP con una "Contraseña de aplicación" en backend/php/core/email_config.php o define una API (BREVO_API_KEY).';
      echo json_encode($debugMode ? array_merge(['success' => false, 'message' => $msg . ' Mientras tanto, usa el enlace directo para restablecer tu contraseña.'], ['reset_url' => $resetUrl]) : ['success' => false, 'message' => $msg . ' Revisa tu correo más tarde.']);
      exit;
    }
    if (SMTP_HOST === 'smtp.gmail.com' && !preg_match('/^[A-Za-z0-9]{16}$/', SMTP_PASS)) {
      $msg = 'Tu contraseña SMTP debe ser una "Contraseña de aplicación" de 16 caracteres alfanuméricos (sin espacios). O usa una API como Brevo para evitar bloqueos de red.';
      echo json_encode($debugMode ? array_merge(['success' => false, 'message' => $msg], ['reset_url' => $resetUrl]) : ['success' => false, 'message' => $msg]);
      exit;
    }
  }

  if ($prov === 'http_brevo' && defined('BREVO_API_KEY') && BREVO_API_KEY !== '') {
    error_log('[reset] Using Brevo API, key starts with: ' . substr(BREVO_API_KEY, 0, 8));
    $send = http_send_mail(
      $email,
      $subject,
      $message,
      MAIL_FROM,
      MAIL_FROM_NAME,
      [ 'provider' => 'brevo', 'api_key' => BREVO_API_KEY ],
      'text/plain'
    );
  } elseif ($prov === 'http_resend' && defined('RESEND_API_KEY') && RESEND_API_KEY !== '') {
    $send = http_send_mail(
      $email,
      $subject,
      $message,
      MAIL_FROM,
      MAIL_FROM_NAME,
      [ 'provider' => 'resend', 'api_key' => RESEND_API_KEY ],
      'text/plain'
    );
  } elseif ($prov === 'http_sendgrid' && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') {
    $send = http_send_mail(
      $email,
      $subject,
      $message,
      MAIL_FROM,
      MAIL_FROM_NAME,
      [ 'provider' => 'sendgrid', 'api_key' => SENDGRID_API_KEY ],
      'text/plain'
    );
  } else {
    $send = smtp_send_mail(
      $email,
      $subject,
      $message,
      MAIL_FROM,
      MAIL_FROM_NAME,
      [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'secure' => SMTP_SECURE,
        'user' => SMTP_USER,
        'pass' => SMTP_PASS,
      ]
    );
  }
  try { error_log('[reset] send_result ok=' . ($send['ok'] ? '1' : '0') . ' err=' . ($send['error'] ?? '')); } catch (Throwable $__) {}

  if (!$send['ok']) {
    $err = $send['error'] ?? 'Error desconocido';
    error_log('SMTP error: ' . $err);
    $isSmtpTimeout = (strpos($err, 'Conexión fallida') !== false || strpos($err, 'timed out') !== false || strpos($err, '(110)') !== false);
    $friendly = ($prov === 'smtp' && $isSmtpTimeout)
      ? 'El servidor no permite conexiones SMTP salientes. Revisa tu correo más tarde.'
      : 'No se pudo enviar el correo (API). Intenta más tarde.';
    $resp = ['success' => false, 'message' => $friendly];
    if ($debugMode) $resp['reset_url'] = $resetUrl;
    echo json_encode($resp);
  }
  else {
    echo json_encode(['success' => true, 'message' => 'Te enviamos el enlace para restablecer la contraseña. Revisa tu correo.']);
  }
} catch (Throwable $e) {
  error_log('request_password_reset.php error: ' . $e->getMessage());
  echo json_encode(['success' => false, 'message' => 'Error en el servidor. Intenta más tarde.']);
}

// PDO no requiere cierre explícito
?>
