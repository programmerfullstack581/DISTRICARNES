<?php
session_start();

// Establecer cabecera JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión
include_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/email_config.php';
require_once __DIR__ . '/../core/smtp_mailer.php';
require_once __DIR__ . '/../core/rate_limit.php';

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
$identifier = $_POST['email'] ?? $_POST['identifier'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
    exit;
}

// =========================================
// RATE LIMIT - Evitar fuerza bruta
// =========================================
$rlIdKey = 'login:id:' . strtolower(trim($identifier));
$rlIpKey = 'login:ip:' . dc_client_ip();

// Bloqueo rápido sin consumir intentos si ya está bloqueado
$peekIp = dc_rate_limit_peek($rlIpKey, 15, 900);
if (!$peekIp['allowed']) {
    $mins = (int)ceil($peekIp['retry_after'] / 60);
    echo json_encode(['success' => false, 'message' => 'Demasiados intentos fallidos. Intenta de nuevo en ' . $mins . ' minuto(s).']);
    exit;
}
$peekId = dc_rate_limit_peek($rlIdKey, 5, 900);
if (!$peekId['allowed']) {
    $mins = (int)ceil($peekId['retry_after'] / 60);
    echo json_encode(['success' => false, 'message' => 'Demasiados intentos para esta cuenta. Intenta de nuevo en ' . $mins . ' minuto(s).']);
    exit;
}

// Registrar el intento (IP + identificador) antes de validar credenciales
$rlIp = dc_rate_limit_consume($rlIpKey, 15, 900);
$rlId = dc_rate_limit_consume($rlIdKey, 5, 900);
if (!$rlIp['allowed'] || !$rlId['allowed']) {
    $retry = max($rlIp['retry_after'], $rlId['retry_after']);
    $mins = (int)ceil($retry / 60);
    echo json_encode(['success' => false, 'message' => 'Demasiados intentos fallidos. Intenta de nuevo en ' . $mins . ' minuto(s).']);
    exit;
}

try {
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified BOOLEAN NOT NULL DEFAULT FALSE"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_token_hash VARCHAR(64) NULL"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_expires TIMESTAMP NULL"); } catch (Throwable $_) {}

    // Detectar columnas disponibles
    $cols = [];
    $stmtCols = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'usuario'");
    while ($r = $stmtCols->fetch(PDO::FETCH_ASSOC)) { $cols[] = $r['column_name']; }
    $stmtCols->closeCursor();

    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

    // Construir cláusulas WHERE para email, nombre o cédula
    $whereParts = [];
    $params = [];

    if (in_array('correo_electronico', $cols) && $isEmail) {
        $whereParts[] = "correo_electronico = ?";
        $params[] = $identifier;
    }

    if (in_array('nombres_completos', $cols)) {
        $whereParts[] = "LOWER(TRIM(nombres_completos)) = LOWER(TRIM(?))";
        $params[] = $identifier;
    }

    // Nombre de usuario (columna usuario_usuario)
    if (in_array('usuario_usuario', $cols)) {
        $whereParts[] = "LOWER(TRIM(usuario_usuario)) = LOWER(TRIM(?))";
        $params[] = $identifier;
    }

    if (in_array('cedula', $cols)) {
        $whereParts[] = "CAST(cedula AS TEXT) = ?";
        $params[] = trim($identifier);
    }

    if (empty($whereParts)) {
        echo json_encode(['success' => false, 'message' => 'No se pudo identificar el campo de búsqueda.']);
        exit;
    }

    $sql = "SELECT id_usuario, nombres_completos, correo_electronico, contrasena, rol, email_verified 
            FROM usuario 
            WHERE " . implode(' OR ', $whereParts) . " LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (!password_verify($password, $user['contrasena'])) {
            $rlRemaining = dc_rate_limit_peek($rlIdKey, 5, 900)['count'];
            $left = max(0, 5 - $rlRemaining);
            $msg = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
            if ($left > 0 && $left <= 2) {
                $msg .= ' Te quedan ' . $left . ' intento(s).';
            }
            echo json_encode(['success' => false, 'message' => $msg]);
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
                $userEmail = $user['correo_electronico'] ?? '';
                $verifyUrl = ($base !== '' ? $base : '') . '/backend/php/auth/guardar_usuario.php?verify=1&email=' . urlencode($userEmail) . '&token=' . urlencode($token);
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
                dc_send_mail_login($userEmail, $subject, $message, 'text/html');
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

        // Prevenir fijación de sesión
        session_regenerate_id(true);

        // Limpiar contadores de intentos fallidos
        dc_rate_limit_reset($rlIdKey);
        dc_rate_limit_reset($rlIpKey);

        // Redirigir según el rol: admin al panel, clientes al catálogo
        $isAdmin = is_string($role) && strtolower($role) === 'admin';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $redirect_url = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($isAdmin ? '/admin/admin_dashboard.html' : '/index.php');

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
