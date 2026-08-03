<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/conexion.php';

try {
  // Crear tabla usuario si no existe (idempotente)
  $conexion->exec("
    CREATE TABLE IF NOT EXISTS usuario (
      id_usuario SERIAL PRIMARY KEY,
      nombres_completos VARCHAR(255) NOT NULL,
      cedula VARCHAR(50) NOT NULL,
      direccion VARCHAR(255),
      celular VARCHAR(20),
      correo_electronico VARCHAR(100) NOT NULL,
      contrasena VARCHAR(255) NOT NULL,
      rol VARCHAR(10) NOT NULL DEFAULT 'trabajo',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      usuario_usuario VARCHAR(50),
      usuario_foto VARCHAR(200),
      caja_id INTEGER,
      usuario_nombre VARCHAR(100),
      usuario_apellido VARCHAR(100),
      email_verified BOOLEAN DEFAULT FALSE,
      email_verified_at TIMESTAMP NULL,
      email_verify_token_hash VARCHAR(64) NULL,
      email_verify_expires TIMESTAMP NULL
    )
  ");

  // Verificar si existe el admin fijo
  $checkAdmin = $conexion->prepare("SELECT id_usuario FROM usuario WHERE LOWER(correo_electronico) = LOWER('districarneshermanosnavarro@gmail.com')");
  $checkAdmin->execute();
  $adminExists = $checkAdmin->fetch();

  if (!$adminExists) {
    // Insertar admin fijo
    $passHash = password_hash('DistriCarnes2024!', PASSWORD_BCRYPT);
    $insertAdmin = $conexion->prepare("INSERT INTO usuario (nombres_completos, cedula, direccion, celular, correo_electronico, contrasena, rol, email_verified, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    $insertAdmin->execute(['Admin Principal', '1234567890', 'Cartagena', '3015210177', 'districarneshermanosnavarro@gmail.com', $passHash, 'admin', true]);
  }

  // Obtener usuarios
  $stmt = $conexion->query("SELECT id_usuario, nombres_completos, cedula, direccion, celular, correo_electronico, rol, created_at FROM usuario ORDER BY id_usuario DESC");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok' => true, 'count' => count($users), 'users' => $users], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
