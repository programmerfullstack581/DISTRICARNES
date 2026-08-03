<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ob_start();
$response = ['success' => false, 'message' => 'An unknown error occurred.'];
try {
    require_once __DIR__ . '/../core/conexion.php';
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified BOOLEAN NOT NULL DEFAULT FALSE"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_token_hash VARCHAR(64) NULL"); } catch (Throwable $_) {}
    try { $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS email_verify_expires TIMESTAMP NULL"); } catch (Throwable $_) {}
    if (empty($_POST['credential'])) throw new Exception('No se recibió el token de Google.');
    $id_token = $_POST['credential'];
    $googleClientId = getenv('GOOGLE_CLIENT_ID') ?: '1089395533199-070ohtiul6msdderh593mlp8m7v7lv3j.apps.googleusercontent.com';
    $payload = null;
    $autoload = __DIR__ . '/../../../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        if (class_exists('Google_Client')) {
            $gc = new Google_Client();
            $gc->setClientId($googleClientId);
            $payload = $gc->verifyIdToken($id_token);
        }
    }
    if (!$payload) {
        $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if (!$res) throw new Exception('Fallo al verificar token con Google: ' . ($err ?: 'sin respuesta'));
        $pj = json_decode($res, true);
        if (!is_array($pj)) throw new Exception('Respuesta inválida de Google.');
        if (($pj['aud'] ?? '') !== $googleClientId) throw new Exception('El token no corresponde a este cliente.');
        if (isset($pj['exp']) && intval($pj['exp']) < time()) throw new Exception('Token expirado.');
        $payload = [
            'sub' => $pj['sub'] ?? null,
            'email' => $pj['email'] ?? null,
            'name' => $pj['name'] ?? ($pj['given_name'] ?? 'Usuario Google'),
            'picture' => $pj['picture'] ?? null
        ];
    }
    if (!$payload) throw new Exception('Token inválido o expirado.');
    $userid = $payload['sub'] ?? null;
    $email = $payload['email'] ?? null;
    $name = $payload['name'] ?? 'Usuario Google';
    $picture = $payload['picture'] ?? null;
    if (empty($email)) throw new Exception('No se pudo obtener el correo electrónico de Google.');
    $stmt = $conexion->prepare("SELECT id_usuario, nombres_completos, correo_electronico, rol, usuario_foto, email_verified FROM usuario WHERE correo_electronico = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        // Actualizar foto o nombre si hay cambios
        try {
            $updateNeeded = false;
            $newName = $name;
            $newPhoto = $picture;
            if (($user['nombres_completos'] ?? '') === '' && $newName) $updateNeeded = true;
            if ($newPhoto && (($user['usuario_foto'] ?? '') !== $newPhoto)) $updateNeeded = true;
            if ($updateNeeded) {
                $stmtUp = $conexion->prepare("UPDATE usuario SET nombres_completos = COALESCE(NULLIF(?, ''), nombres_completos), usuario_foto = COALESCE(?, usuario_foto), email_verified = TRUE, email_verified_at = COALESCE(email_verified_at, CURRENT_TIMESTAMP), email_verify_token_hash = NULL, email_verify_expires = NULL, updated_at = CURRENT_TIMESTAMP WHERE id_usuario = ?");
                $stmtUp->execute([$newName, $newPhoto, $user['id_usuario']]);
                // refrescar datos
                $stmt = $conexion->prepare("SELECT id_usuario, nombres_completos, correo_electronico, rol, usuario_foto, email_verified FROM usuario WHERE id_usuario = ?");
                $stmt->execute([$user['id_usuario']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: $user;
            }
        } catch (\Throwable $t) { /* no-op */ }
        if (!($user['email_verified'] ?? false)) {
            try {
                $stmtUp = $conexion->prepare("UPDATE usuario SET email_verified = TRUE, email_verified_at = COALESCE(email_verified_at, CURRENT_TIMESTAMP), email_verify_token_hash = NULL, email_verify_expires = NULL, updated_at = CURRENT_TIMESTAMP WHERE id_usuario = ?");
                $stmtUp->execute([$user['id_usuario']]);
                $stmtUp->closeCursor();
                $stmt = $conexion->prepare("SELECT id_usuario, nombres_completos, correo_electronico, rol, usuario_foto, email_verified FROM usuario WHERE id_usuario = ?");
                $stmt->execute([$user['id_usuario']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: $user;
            } catch (\Throwable $_) {}
        }
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;
        $redirect_url = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
        $response = [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id_usuario'],
                'nombre' => $user['nombres_completos'],
                'email' => $user['correo_electronico'],
                'rol' => $user['rol'],
                'foto' => $user['usuario_foto'] ?? $picture
            ],
            'redirect_url' => $redirect_url
        ];
    } else {
        // Rol por defecto compatible con restricción CHECK
        $rol = 'trabajo';
        $cedula = substr($userid ?: bin2hex(random_bytes(8)), 0, 20);
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        $direccion_placeholder = 'No especificada';
        $celular_placeholder = '0000000000';
        $stmt_insert = $conexion->prepare("INSERT INTO usuario (nombres_completos, correo_electronico, rol, cedula, contrasena, direccion, celular, usuario_foto, email_verified, email_verified_at, email_verify_token_hash, email_verify_expires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, CURRENT_TIMESTAMP, NULL, NULL)");
        if ($stmt_insert->execute([$name, $email, $rol, $cedula, $password, $direccion_placeholder, $celular_placeholder, $picture])) {
            $new_user_id = $conexion->lastInsertId();
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_rol'] = $rol;
            $_SESSION['logged_in'] = true;
            $redirect_url = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
            $response = [
                'success' => true,
                'message' => 'Registration and login successful',
                'user' => [
                    'id' => $new_user_id,
                    'nombre' => $name,
                    'email' => $email,
                    'rol' => $rol,
                    'foto' => $picture
                ],
                'redirect_url' => $redirect_url
            ];
        } else {
            $errorInfo = $stmt_insert->errorInfo();
            throw new Exception('Error creando usuario: ' . ($errorInfo[2] ?? 'Operación fallida en la base de datos'));
        }
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Google Login Error: ' . $e->getMessage());
}
$output = ob_get_clean();
if (!empty($output)) {
    error_log("Google Login discarded output: " . $output);
}
echo json_encode($response);
exit;
