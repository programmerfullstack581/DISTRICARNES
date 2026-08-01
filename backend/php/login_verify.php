<?php
session_start();

// Establecer cabecera JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión
include_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/smtp_mailer.php';

function dc_mail_provider_login(): string {
    $prov = null;
    if (defined('BREVO_API_KEY') && BREVO_API_KEY !== '') $prov = 'http_brevo';
    elseif (defined('RESEND_API_KEY') && RESEND_API_KEY !== '') $prov = 'http_resend';
    elseif (defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') $prov = 'http_sendgrid';
    if (!$prov && defined('MAIL_PROVIDER')) {
        $forced = strtolower(trim(MAIL_PROVIDER));
        if ($forced === 'http_brevo' && defined('BREVO_API_KEY') && BREVO_API_KEY !== '') $prov = 'http_brevo';
        elseif ($forced === 'http_resend' && defined('RESEND_API_KEY') && RESEND_API_KEY !== '') $prov = 'http_resend';
        elseif ($forced === 'http_sendgrid' && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') $prov = 'http_sendgrid';
        elseif ($forced === 'smtp') $prov = 'smtp';
    }
    if (!$prov) $prov = 'smtp';
    return $prov;
}

function dc_send_mail_login(string $to, string $subject, string $body, string $contentType = 'text/html'): array {
    $prov = dc_mail_provider_login();
    if ($prov === 'http_brevo' && defined('BREVO_API_KEY') && BREVO_API_KEY !== '') {
        return http_send_mail($to, $subject, $body, MAIL_FROM, MAIL_FROM_NAME, [ 'provider' => 'brevo', 'api_key' => BREVO_API_KEY ], $contentType);
    }
    if ($prov === 'http_resend' && defined('RESEND_API_KEY') && RESEND_API_KEY !== '') {
        return http_send_mail($to, $subject, $body, MAIL_FROM, MAIL_FROM_NAME, [ 'provider' => 'resend', 'api_key' => RESEND_API_KEY ], $contentType);
    }
    if ($prov === 'http_sendgrid' && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') {
        return http_send_mail($to, $subject, $body, MAIL_FROM, MAIL_FROM_NAME, [ 'provider' => 'sendgrid', 'api_key' => SENDGRID_API_KEY ], $contentType);
    }
    return smtp_send_mail($to, $subject, $body, MAIL_FROM, MAIL_FROM_NAME, [
        'host' => SMTP_HOST, 'port' => SMTP_PORT, 'secure' => SMTP_SECURE, 'user' => SMTP_USER, 'pass' => SMTP_PASS
    ], $contentType);
}

function dc_base_url_login(): string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') return '';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    return $scheme . '://' . $host;
}

// Solo permitir POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Validar que existan los campos
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
    exit;
}

try {
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified BOOLEAN NOT NULL DEFAULT FALSE"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_token_hash VARCHAR(64) NULL"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_expires TIMESTAMP NULL"); } catch (Throwable $_) {}

    // Buscar por email y verificar hash de contraseña
    $sql = "SELECT id_usuario, nombres_completos, correo_electronico, contrasena, rol, email_verified 
            FROM usuario 
            WHERE correo_electronico = ? LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$email]);
    
    // En PDO obtenemos el resultado directamente con fetch()
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (!password_verify($password, $user['contrasena'])) {
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas. Verifica tu correo y contraseña.']);
            $stmt->closeCursor();
            exit;
        }

        $role = $user['rol'] ?? '';
        $isVerified = (bool)($user['email_verified'] ?? false);
        if ($role !== 'admin' && !$isVerified) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 24 * 60 * 60);

            try {
                $stmtU = $conexion->prepare("UPDATE usuario SET email_verify_token_hash = ?, email_verify_expires = ?, updated_at = CURRENT_TIMESTAMP WHERE id_usuario = ?");
                $stmtU->execute([$tokenHash, $expiresAt, $user['id_usuario']]);
                $stmtU->closeCursor();
            } catch (Throwable $_) {}

            try {
                $base = dc_base_url_login();
                $verifyUrl = ($base !== '' ? $base : '') . '/backend/php/guardar_usuario.php?verify=1&email=' . urlencode($email) . '&token=' . urlencode($token);
                $subject = 'Verifica tu correo en DistriCarnes';
                $name = $user['nombres_completos'] ?? 'Usuario';
                $message = '<div style="font-family:Arial,sans-serif;line-height:1.5;color:#111">' .
                    '<h2 style="margin:0 0 12px 0">Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>' .
                    '<p style="margin:0 0 12px 0">Tu cuenta aún no está verificada. Para poder iniciar sesión y comprar, confirma tu correo:</p>' .
                    '<p style="margin:16px 0"><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:12px 18px;background:#e53e3e;color:#fff;text-decoration:none;border-radius:8px">Verificar mi correo</a></p>' .
                    '<p style="margin:0 0 12px 0">Si no puedes abrir el botón, copia y pega este enlace:</p>' .
                    '<p style="margin:0 0 12px 0"><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '</a></p>' .
                    '<p style="margin:0;color:#555;font-size:12px">Este enlace vence en 24 horas.</p>' .
                    '</div>';
                dc_send_mail_login($email, $subject, $message, 'text/html');
            } catch (Throwable $_) {}

            echo json_encode([
                'success' => false,
                'needs_verification' => true,
                'message' => 'Debes verificar tu correo. Te enviamos un enlace de verificación a tu email.'
            ]);
            $stmt->closeCursor();
            exit;
        }

        // Guardar información completa en sesión
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_email'] = $user['correo_electronico'];
        $_SESSION['user_name'] = $user['nombres_completos'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;

        // Redirigir siempre al panel de administración
        $redirect_url = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';

        // Respuesta exitosa con información del usuario
        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user['id_usuario'],
                'nombre' => $user['nombres_completos'],
                'email' => $user['correo_electronico'],
                'rol' => $user['rol']
            ],
            'redirect_url' => $redirect_url
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas. Verifica tu correo y contraseña.']);
    }

    $stmt->closeCursor();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor. Inténtalo más tarde.']);
    error_log("Error en login_verify.php: " . $e->getMessage());
}

// $conexion->close(); No necesario en PDO, se cierra al acabar el script
?>
