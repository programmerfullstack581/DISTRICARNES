<?php
// =============================================
// CSRF - Protección anti falsificación de peticiones
// =============================================
// Sesión PHP + token por sesión. Incluir con require_once.
// Funciones:
//   dc_csrf_start()   - inicia sesión (idempotente, con cookies seguras).
//   dc_csrf_token()   - devuelve el token actual (lo crea si no existe).
//   dc_csrf_verify()  - valida token del header X-CSRF-Token o del JSON (_csrf).
//   dc_csrf_require() - valida y responde 403 JSON si falla.
// Endpoint público para obtener el token: backend/php/core/csrf_token.php

if (!function_exists('dc_csrf_start')) {
    function dc_csrf_start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            ]);
            session_start();
        }
    }

    function dc_csrf_token(): string {
        dc_csrf_start();
        if (empty($_SESSION['dc_csrf_token'])) {
            $_SESSION['dc_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['dc_csrf_token'];
    }

    function dc_csrf_submitted_token(): string {
        $fromHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if ($fromHeader !== '') return $fromHeader;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'PUT' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'PATCH' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'DELETE') {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['_csrf'])) {
                return (string)$data['_csrf'];
            }
        }
        return $_POST['_csrf'] ?? '';
    }

    function dc_csrf_verify(): bool {
        dc_csrf_start();
        $token = $_SESSION['dc_csrf_token'] ?? '';
        $sent  = dc_csrf_submitted_token();
        return $token !== '' && $sent !== '' && hash_equals($token, $sent);
    }

    function dc_csrf_require(): void {
        if (!dc_csrf_verify()) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
            }
            echo json_encode(['ok' => false, 'success' => false, 'error' => 'Token CSRF inválido o ausente.']);
            exit;
        }
    }
}
