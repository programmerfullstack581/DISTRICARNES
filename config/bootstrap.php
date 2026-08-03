<?php
// =============================================
// config/bootstrap.php
// Carga base para todas las páginas del sitio.
// Unifica el cálculo de $basePath, la configuración
// global (app.php) y las utilidades de la app.
// =============================================
if (defined('DISTRICARNES_BOOTSTRAP_LOADED')) {
    return;
}
define('DISTRICARNES_BOOTSTRAP_LOADED', true);

// ---- $basePath: ruta relativa hacia la RAÍZ del sitio ----
// Sube desde el directorio del script actual hasta encontrar
// static/ (marca de la raíz del proyecto), así funciona desde
// cualquier profundidad (raíz, /pages, /admin, /login, ...).
$basePath = '';
if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $resolved = realpath($scriptFile);
    if ($resolved !== false) {
        $scriptFile = str_replace('\\', '/', $resolved);
    }
    $dir = dirname($scriptFile);
    $prev = '';
    $found = false;
    while ($dir && $dir !== $prev) {
        if (is_dir($dir . '/static') || is_dir($dir . '/backend')) {
            $found = true;
            break;
        }
        $prev = $dir;
        $dir = dirname($dir);
    }
    if ($found && !empty($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        if ($docRoot && strpos($dir, $docRoot) === 0) {
            $basePath = substr($dir, strlen($docRoot));
        }
    }
}
$basePath = str_replace('\\', '/', $basePath);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
$basePath = rtrim($basePath, '/');

// ---- Configuración global (BASE_URL, BASE_PATH, asset(), url(), ...) ----
require_once __DIR__ . '/app.php';

// Nota: la conexión a BD la carga cada página que la necesita con
// require_once __DIR__ . '/backend/php/core/conexion.php';
