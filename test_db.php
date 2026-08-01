<?php
header('Content-Type: application/json; charset=utf-8');

$host = getenv('HOST') ?: '';
$port = getenv('DB_PORT') ?: '6543';
$database = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

$response = [
    'env_vars' => [
        'HOST' => $host ?: '(vacío)',
        'DB_PORT' => $port ?: '6543 (default)',
        'DB_NAME' => $database ?: '(vacío)',
        'DB_USER' => $username ?: '(vacío)',
        'DB_PASSWORD' => $password ? '(configurado)' : '(vacío)'
    ]
];

if (empty($host) || empty($database) || empty($username) || empty($password)) {
    $response['status'] = 'FALTAN_VARIABLES';
    $response['message'] = 'Las variables de entorno no están configuradas';
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
    $conexion = new PDO($dsn, $username, $password);
    $response['status'] = 'CONECTADO';
    $response['message'] = 'Conexión exitosa a la base de datos';
    
    // Listar tablas
    $stmt = $conexion->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $response['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Contar usuarios
    if (in_array('usuario', $response['tables'])) {
        $stmt2 = $conexion->query("SELECT COUNT(*) as total FROM usuario");
        $response['usuario_count'] = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        $stmt3 = $conexion->query("SELECT id_usuario, nombres_completos, correo_electronico, rol FROM usuario ORDER BY id_usuario DESC LIMIT 20");
        $response['usuarios'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $response['status'] = 'ERROR';
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>