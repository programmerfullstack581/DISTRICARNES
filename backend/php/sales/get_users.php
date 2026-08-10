<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/admin_auth.php';
require_once __DIR__ . '/../core/conexion.php';

try {
  // La tabla puede existir con un esquema distinto (o el rol de BD puede no
  // tener permisos DDL). Asegurar columnas de forma idempotente y nunca
  // dejar que el bootstrap de la tabla tumbe el endpoint.
  $ensureCols = [
    "email_verified BOOLEAN NOT NULL DEFAULT FALSE",
    "email_verified_at TIMESTAMP NULL",
    "email_verify_token_hash VARCHAR(64) NULL",
    "email_verify_expires TIMESTAMP NULL",
  ];
  foreach ($ensureCols as $colSql) {
    try {
      $conexion->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS $colSql");
    } catch (Throwable $_) { /* tabla inexistente o sin permisos DDL: continuar */ }
  }

  // Detectar las columnas realmente disponibles para construir una consulta segura.
  $available = [];
  try {
    $stmtCols = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'usuario'");
    while ($row = $stmtCols->fetch(PDO::FETCH_ASSOC)) {
      $available[] = $row['column_name'];
    }
    $stmtCols->closeCursor();
  } catch (Throwable $_) { /* tabla inexistente */ }

  if (empty($available)) {
    // Crear la tabla solo si no existe y la consulta de columnas no devolvió nada.
    try {
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
          email_verified BOOLEAN DEFAULT FALSE
        )
      ");
      $available = [
        'id_usuario', 'nombres_completos', 'cedula', 'direccion', 'celular',
        'correo_electronico', 'rol', 'created_at',
      ];
    } catch (Throwable $e) {
      http_response_code(500);
      error_log('get_users.php: ' . $e->getMessage());
      echo json_encode(['ok' => false, 'error' => 'Error del servidor']);
      exit;
    }
  }

  // Asegurar que el admin fijo exista (solo si la tabla es escribible).
  try {
    $checkAdmin = $conexion->prepare("SELECT id_usuario FROM usuario WHERE LOWER(correo_electronico) = LOWER('districarneshermanosnavarro@gmail.com')");
    $checkAdmin->execute();
    $adminExists = $checkAdmin->fetch();
    $checkAdmin->closeCursor();

    if (!$adminExists) {
      $passHash = password_hash('DistriCarnes2024!', PASSWORD_BCRYPT);
      $insertAdmin = $conexion->prepare("INSERT INTO usuario (nombres_completos, cedula, direccion, celular, correo_electronico, contrasena, rol, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $insertAdmin->execute(['Admin Principal', '1234567890', 'Cartagena', '3015210177', 'districarneshermanosnavarro@gmail.com', $passHash, 'admin', true]);
    }
  } catch (Throwable $_) { /* sin permisos DDL/escritura: no bloquear la lectura */ }

  // Seleccionar solo columnas existentes para soportar esquemas heredados.
  $wanted = [
    'id_usuario', 'nombres_completos', 'cedula', 'direccion', 'celular',
    'correo_electronico', 'rol', 'created_at',
  ];
  $cols = array_values(array_intersect($wanted, $available));
  if (empty($cols)) {
    throw new RuntimeException('No se encontraron columnas válidas en la tabla usuario');
  }
  $select = implode(', ', $cols);
  $stmt = $conexion->query("SELECT $select FROM usuario ORDER BY id_usuario DESC");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok' => true, 'count' => count($users), 'users' => $users], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
  http_response_code(500);
  error_log('get_users.php: ' . $e->getMessage());
  $resp = ['ok' => false, 'error' => 'Error del servidor'];
  if (getenv('DC_DEBUG') === '1') {
    $resp['error_details'] = $e->getMessage();
  }
  echo json_encode($resp);
}
?>
