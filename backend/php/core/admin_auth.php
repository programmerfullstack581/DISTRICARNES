<?php
// =========================================
// AUTORIZACIÓN - Guard de endpoints administrativos
// =========================================
// Verifica la sesión PHP y que el rol sea 'admin'.
// Incluir con require_once en cualquier endpoint de admin/ventas/órdenes.
// El guard se ejecuta automáticamente al incluir el archivo.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

if (!function_exists('dc_is_admin_session')) {
    function dc_is_admin_session(): bool {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        $role = $_SESSION['rol'] ?? ($_SESSION['user_rol'] ?? '');
        return is_string($role) && strtolower($role) === 'admin';
    }
}

if (!dc_is_admin_session()) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
    }
    echo json_encode(['ok' => false, 'success' => false, 'error' => 'Acceso no autorizado.']);
    exit;
}
