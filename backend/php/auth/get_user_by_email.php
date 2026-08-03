<?php
// Devuelve información básica del usuario por email: nombre, rol y foto
header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/../core/conexion.php';
    $email = trim($_POST['email'] ?? ($_GET['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']); exit;
    }
    // Solo se puede consultar la información del usuario logueado (evita enumeración de cuentas)
    $sessionEmail = $_SESSION['user_email'] ?? '';
    if ($sessionEmail === '' || strtolower($sessionEmail) !== strtolower($email)) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']); exit;
    }
    $stmt = $conexion->prepare("SELECT id_usuario, nombres_completos, correo_electronico, rol, usuario_foto FROM usuario WHERE correo_electronico = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']); exit;
    }
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id_usuario'],
            'nombre' => $user['nombres_completos'],
            'email' => $user['correo_electronico'],
            'rol' => $user['rol'],
            'foto' => $user['usuario_foto']
        ]
    ]);
} catch (Exception $e) {
    error_log('get_user_by_email.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
exit;

