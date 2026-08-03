<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443 ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

if (!isset($basePath)) {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/';
    $basePath = dirname($scriptName);
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }

    $basePath = str_replace('\\', '/', $basePath);
}

if (isset($_SERVER['HTTP_X_BASE_PATH'])) {
    $basePath = $_SERVER['HTTP_X_BASE_PATH'];
}

define('BASE_URL', $protocol . $host . $basePath);
define('BASE_PATH', $basePath);

if ($basePath === '/VENTAS' || $basePath === '') {
    $isLocal = ($basePath === '/VENTAS');
} else {
    $isLocal = false;
}

define('IS_LOCAL', $isLocal);

$envKey = getenv('GOOGLE_MAPS_API_KEY');
if (!$envKey && isset($_ENV['GOOGLE_MAPS_API_KEY'])) {
    $envKey = $_ENV['GOOGLE_MAPS_API_KEY'];
}
define('GOOGLE_MAPS_API_KEY', $envKey ? $envKey : '');

function asset($path) {
    $path = ltrim($path, '/');
    return BASE_PATH . '/' . $path;
}

function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_PATH . '/' . $path;
}

function base_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}