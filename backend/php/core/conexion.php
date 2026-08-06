<?php
// =========================================
// SEGURIDAD - Include automático
// =========================================
if (!defined('BYPASS_SECURITY')) {
    require_once __DIR__ . '/security.php';
}

// ===============================
// Configuración de la base de datos
// ===============================

// Zona horaria por defecto: Bogotá, Colombia
date_default_timezone_set('America/Bogota');

$host = getenv('HOST') ?: '';
$port = getenv('DB_PORT') ?: '';
$database = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

// ===============================
// Verificar que existan las variables
// ===============================

if (empty($host) || empty($database) || empty($username) || empty($password)) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    $resp = [
        'success' => false,
        'message' => 'Faltan variables de entorno para la conexión.',
    ];
    // Detalles solo en entorno de depuración explícito
    if (getenv('DC_DEBUG') === '1') {
        $resp['error_details'] = 'Verifica HOST, DB_NAME, DB_USER y DB_PASSWORD en Render';
        $resp['debug'] = [
            'host_empty' => empty($host),
            'database_empty' => empty($database),
            'username_empty' => empty($username),
            'password_empty' => empty($password)
        ];
    }
    echo json_encode($resp);
    exit();
}

// ===============================
// Verificar driver PostgreSQL
// ===============================

if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Falta habilitar el driver de PostgreSQL para PDO.',
        'error_details' => 'pdo_pgsql no está disponible en php.ini'
    ]);
    exit();
}

// ===============================
// Crear conexión con PDO PostgreSQL
// ===============================

try {
    // DSN para Session Pooler de Supabase
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
    
    $conexion = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Forzar UTF-8
    $conexion->exec("SET NAMES 'UTF8'");
    // Establecer zona horaria de la sesión de la BD a Colombia
    $conexion->exec("SET TIME ZONE 'America/Bogota'");

} catch (PDOException $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    $resp = [
        'success' => false,
        'message' => 'Error de conexión a PostgreSQL.',
    ];
    // Nunca exponer host/puerto/usuario/DSN ni mensajes internos fuera de depuración
    if (getenv('DC_DEBUG') === '1') {
        $resp['error_details'] = $e->getMessage();
        $resp['debug_info'] = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'dsn' => $dsn ?? 'No generado'
        ];
    }
    error_log('Conexión PostgreSQL falló: ' . $e->getMessage());
    echo json_encode($resp);

    exit();
}

// conexion exitosa a la base de datos districarnes que se encuentra en supabase 

?>
