<?php
// Devuelve información básica del usuario por email: nombre, rol y foto
header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/conexion.php';
    if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']); exit;
    }
    $email = trim($_POST['email']);
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
    echo json_encode(['success' => false, 'message' => 'Error del servidor', 'detail' => $e->getMessage()]);
}
exit;

