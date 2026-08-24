<?php
// Archivo de diagnóstico - prueba mínima del runtime vercel-php
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'ok',
    'php_version' => PHP_VERSION,
    'extensions' => get_loaded_extensions(),
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'root_dir' => dirname(__DIR__),
    'root_files' => is_dir(dirname(__DIR__)) ? scandir(dirname(__DIR__)) : 'cannot scan',
    'composer_json_exists' => file_exists(dirname(__DIR__) . '/composer.json'),
    'vendor_exists' => is_dir(dirname(__DIR__) . '/vendor'),
    'index_php_exists' => file_exists(dirname(__DIR__) . '/index.php'),
    'config_dir_exists' => is_dir(dirname(__DIR__) . '/config'),
    'includes_dir_exists' => is_dir(dirname(__DIR__) . '/includes'),
    'memory_limit' => ini_get('memory_limit'),
    'temp_dir' => sys_get_temp_dir(),
]);
