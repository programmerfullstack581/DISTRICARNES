<?php
// ==========================================================
// VERCEL SERVERLESS PHP GATEWAY - DISTRICARNES
// ==========================================================

// Prevenir caída de la función Lambda capturando cualquier error o excepción fatal
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

// Manejador de shutdown para capturar errores fatales de PHP y mostrarlos claramente
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<div style="font-family:sans-serif;padding:2rem;max-width:800px;margin:2rem auto;background:#fff1f0;border:1px solid #ffa39e;border-radius:8px;">';
        echo '<h2 style="color:#cf1322;margin-top:0;">Error del Servidor (PHP Fatal Error)</h2>';
        echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($error['message'], ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($error['file'], ENT_QUOTES, 'UTF-8') . ' (Línea ' . $error['line'] . ')</p>';
        echo '</div>';
    }
});

// Configuración de Sesiones en /tmp (único directorio con permisos de escritura en Vercel Serverless)
$tempDir = sys_get_temp_dir() ?: '/tmp';
if (is_dir($tempDir) && is_writable($tempDir)) {
    session_save_path($tempDir);
    ini_set('session.save_path', $tempDir);
}

// Obtener y normalizar la URI solicitada
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = explode('?', $uri)[0];
$uri = ltrim($uri, '/');

// Directorio raíz del proyecto
$rootDir = dirname(__DIR__);

// Seguridad - Bloqueos emulando .htaccess
if (str_starts_with(basename($uri), '.')) {
    http_response_code(403);
    echo "403 Forbidden - Acceso Denegado";
    exit;
}

$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
$blockedExtensions = ['env', 'json', 'md', 'sql', 'log', 'txt', 'pem', 'key', 'yml', 'yaml', 'xml', 'dockerignore', 'lock'];
if (in_array($ext, $blockedExtensions, true)) {
    http_response_code(403);
    echo "403 Forbidden - Extensión Protegida";
    exit;
}

$filename = basename($uri);
if (in_array($filename, ['conexion.php', 'config.php', 'security.php'], true)) {
    http_response_code(403);
    echo "403 Forbidden - Archivo Protegido";
    exit;
}

if (str_starts_with($uri, 'db/') || $uri === 'db' || str_starts_with($uri, 'config/') || $uri === 'config' || str_starts_with($uri, 'tests/') || $uri === 'tests') {
    http_response_code(403);
    echo "403 Forbidden - Directorio Protegido";
    exit;
}

if (preg_match('/^backend\/php\/(.*)\.php$/i', $uri, $matches)) {
    $subpath = $matches[1];
    $allowed = [
        'auth/google_login',
        'auth/login_verify',
        'auth/guardar_usuario',
        'auth/facebook_login',
        'auth/request_password_reset',
        'auth/perform_password_reset',
        'auth/get_user_by_email',
        'auth/ensure_session',
        'catalog/get_products',
        'catalog/get_categories',
        'catalog/get_offers',
        'orders/address_list',
        'orders/address_upsert',
        'orders/shipping_quote',
        'orders/orders_save',
        'orders/orders_save_pending',
        'orders/orders_get',
        'orders/orders_list',
        'orders/orders_update_status',
        'orders/order_invoice',
        'orders/send_invoice_email',
        'orders/invoices_list',
        'orders/payment_proof_upload',
        'payments/capture_paypal_order',
        'payments/create_paypal_order',
        'payments/create_payu_order',
        'payments/payu_confirmation',
        'payments/get_paypal_client',
        'payments/paypal_webhook',
        'core/csrf_token',
        'sales/dashboard_stats',
        'sales/notifications_list',
        'sales/sales_list',
        'sales/get_reports',
        'sales/get_users',
        'sales/search_clients',
        'sales/pos_checkout',
        'sales/print_receipt',
        'sales/admin_sales_crud',
        'sales/sales_utils',
        'admin/admin_categories_manage',
        'admin/admin_inventory_manage',
        'admin/admin_products_manage',
        'admin/admin_profile_manage',
        'admin/admin_promotions_manage',
        'admin/admin_settings_manage',
        'admin/admin_users_manage',
        'user/user_profile_manage',
        'user/user_settings_manage',
        'user/profile_photo_upload',
        'tests/test_mail',
        'tests/test_invoices'
    ];
    if (!in_array($subpath, $allowed, true)) {
        http_response_code(403);
        echo "403 Forbidden - Endpoint Interno No Autorizado";
        exit;
    }
}

if (preg_match('/^backend\/api\/(.*)\.php$/i', $uri, $matches)) {
    $subpath = $matches[1];
    if ($subpath !== 'chat') {
        http_response_code(403);
        echo "403 Forbidden - API Interna No Autorizada";
        exit;
    }
}

// Mapeo y Resolución del archivo solicitado
if ($uri === '' || $uri === 'index.php') {
    $target = 'index.php';
} elseif ($uri === 'admin' || $uri === 'admin/') {
    header('Location: /admin/admin_dashboard.html');
    exit;
} elseif ($uri === 'login' || $uri === 'login/') {
    $target = 'login/login.php';
} elseif ($uri === 'carrito-de-compras' || $uri === 'carrito-de-compras/') {
    $target = 'carrito-de-compras/index.php';
} elseif ($uri === 'checkout' || $uri === 'checkout/') {
    $target = 'checkout/direccion.php';
} else {
    $target = $uri;
}

$file = $rootDir . '/' . $target;

if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.php';
}

if (!is_file($file) && is_file($file . '.php')) {
    $file .= '.php';
}

// Ejecución segura de PHP
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    $_SERVER['SCRIPT_FILENAME'] = realpath($file) ?: $file;
    $relPath = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], strlen(realpath($rootDir) ?: $rootDir)));
    $_SERVER['SCRIPT_NAME'] = '/' . ltrim($relPath, '/');
    $_SERVER['PHP_SELF'] = '/' . ltrim($relPath, '/');
    $_SERVER['DOCUMENT_ROOT'] = realpath($rootDir) ?: $rootDir;

    chdir($rootDir);

    $vendorAutoload = $rootDir . '/vendor/autoload.php';
    if (file_exists($vendorAutoload)) {
        require_once $vendorAutoload;
    }

    try {
        require $file;
    } catch (Throwable $e) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<div style="font-family:sans-serif;padding:2rem;max-width:800px;margin:2rem auto;background:#fff1f0;border:1px solid #ffa39e;border-radius:8px;">';
        echo '<h2 style="color:#cf1322;margin-top:0;">Error de Ejecución en la Aplicación</h2>';
        echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ' (Línea ' . $e->getLine() . ')</p>';
        echo '<pre style="background:#f5f5f5;padding:1rem;border-radius:4px;overflow-x:auto;">' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        echo '</div>';
    }
    exit;
}

if (is_file($file)) {
    $mime = mime_content_type($file);
    if ($mime) {
        header("Content-Type: $mime");
    }
    readfile($file);
    exit;
}

http_response_code(404);
echo "404 Not Found - El recurso solicitado no existe.";
exit;
