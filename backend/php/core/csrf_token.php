<?php
// =============================================
// CSRF TOKEN - Endpoint público para obtener token
// El frontend lo usa para incluir X-CSRF-Token en escrituras.
// =============================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/csrf.php';

echo json_encode(['ok' => true, 'token' => dc_csrf_token()]);
exit;
