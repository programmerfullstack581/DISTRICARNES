<?php
// =========================================
// SECURITY - Header de seguridad para todas las páginas
// =========================================

// Iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

// Headers de seguridad HTTP
if (!headers_sent()) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // Content-Security-Policy (allowlist de CDNs realmente usados por el sitio)
    $nonce = base64_encode(random_bytes(16));
    $_SESSION['dc_csp_nonce'] = $nonce;
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://connect.facebook.net https://www.paypal.com https://maps.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: blob: https://images.unsplash.com https://connect.facebook.net https://api.qrserver.com https://maps.google.com https://www.google.com https://maps.googleapis.com https://maps.gstatic.com https://mt0.googleapis.com https://mt1.googleapis.com https://mt2.googleapis.com https://mt3.googleapis.com https://mts0.googleapis.com https://mts1.googleapis.com https://mts2.googleapis.com https://mts3.googleapis.com; connect-src 'self' https://www.paypal.com https://api-m.paypal.com https://api-m.sandbox.paypal.com https://api-sandbox.factus.com.co https://api.brevo.com https://api.resend.com https://api.sendgrid.com https://api.openai.com https://oauth2.googleapis.com https://accounts.google.com https://formspree.io https://maps.googleapis.com; frame-src 'self' https://www.paypal.com https://maps.google.com https://www.google.com https://player.vimeo.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self' https://wa.me https://www.paypal.com https://accounts.google.com");
    // Strict-Transport-Security solo tiene sentido sobre HTTPS; no forzarlo en HTTP (localhost/dev)
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // ANTI-CACHE: Evitar que el navegador guarde la vista del admin al cerrar sesión
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
}

// Función para verificar si el usuario está logueado
function requireAuth() {
    $userData = isset($_SESSION['userData']) ? $_SESSION['userData'] : null;
    $currentSession = isset($_SESSION['currentSession']) ? $_SESSION['currentSession'] : null;
    
    if (!$userData && !$currentSession) {
        if (!headers_sent()) {
            header('Location: ./login/login.php');
        } else {
            echo '<script>window.location.href = "./login/login.php";</script>';
        }
        exit();
    }
    return $userData ?? $currentSession;
}

// Función para verificar rol de administrador
function requireAdmin() {
    $user = requireAuth();
    if (!isset($user['rol']) || $user['rol'] !== 'admin') {
        if (!headers_sent()) {
            header('Location: ./index.php');
        }
        exit();
    }
    return $user;
}

// Función para obtener usuario actual (sin redireccionar)
function getCurrentUser() {
    if (isset($_SESSION['userData'])) {
        return $_SESSION['userData'];
    }
    if (isset($_SESSION['currentSession'])) {
        return $_SESSION['currentSession'];
    }
    return null;
}

// Función para verificar si está logueado
function isLoggedIn() {
    return isset($_SESSION['userData']) || isset($_SESSION['currentSession']);
}

// CSRF Token
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitizar entrada
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Proteger contra acceso directo a archivos PHP
if (!defined('SECURITY_LOADED')) {
    define('SECURITY_LOADED', true);
}
