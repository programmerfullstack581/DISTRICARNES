<?php
// ==========================================================
// VERCEL SERVERLESS PHP GATEWAY - DISTRICARNES
// ==========================================================

// 1. Configuración de Sesiones en entorno Serverless (/tmp es la única carpeta con permisos de escritura en AWS Lambda / Vercel)
if (session_status() === PHP_SESSION_NONE) {
    $tempDir = sys_get_temp_dir();
    if (is_dir($tempDir) && is_writable($tempDir)) {
        ini_set('session.save_path', $tempDir);
    }
}

// 2. Obtener y normalizar la URI solicitada
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = explode('?', $uri)[0]; // Remover query string para la resolución de ruta
$uri = ltrim($uri, '/');

// Directorio raíz del proyecto en el contenedor / función
$rootDir = dirname(__DIR__);

// 3. Emulación de seguridad y bloqueo de accesos no autorizados (.htaccess)

// 3a. Bloquear acceso a archivos ocultos (que empiezan con .)
if (str_starts_with(basename($uri), '.')) {
    http_response_code(403);
    echo "403 Forbidden - Acceso Denegado";
    exit;
}

// 3b. Bloquear extensiones sensibles
$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
$blockedExtensions = ['env', 'json', 'md', 'sql', 'log', 'txt', 'pem', 'key', 'yml', 'yaml', 'xml', 'dockerignore', 'lock'];
if (in_array($ext, $blockedExtensions, true)) {
    http_response_code(403);
    echo "403 Forbidden - Extensión Protegida";
    exit;
}

// 3c. Bloquear acceso directo a archivos clave de configuración y conexión
$filename = basename($uri);
if (in_array($filename, ['conexion.php', 'config.php', 'security.php'], true)) {
    http_response_code(403);
    echo "403 Forbidden - Archivo Protegido";
    exit;
}

// 3d. Bloquear acceso a directorios protegidos
if (str_starts_with($uri, 'db/') || $uri === 'db' || str_starts_with($uri, 'config/') || $uri === 'config' || str_starts_with($uri, 'tests/') || $uri === 'tests') {
    http_response_code(403);
    echo "403 Forbidden - Directorio Protegido";
    exit;
}

// 3e. Bloquear acceso a backend/php/.*.php excepto APIs públicas (lista blanca)
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

// 3f. Bloquear acceso a backend/api/.*.php excepto chat.php
if (preg_match('/^backend\/api\/(.*)\.php$/i', $uri, $matches)) {
    $subpath = $matches[1];
    if ($subpath !== 'chat') {
        http_response_code(403);
        echo "403 Forbidden - API Interna No Autorizada";
        exit;
    }
}

// 4. Mapeo y Resolución del archivo correspondiente en el proyecto
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

// Si es un directorio, buscar index.php
if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.php';
}

// Soporte para URLs limpias (sin .php): si no existe directamente, probar con .php
if (!is_file($file) && is_file($file . '.php')) {
    $file .= '.php';
}

// 5. Si el archivo existe y es PHP, ejecutarlo en el contexto de la raíz
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    // Ajustar variables del servidor para emular ejecución nativa
    $_SERVER['SCRIPT_FILENAME'] = realpath($file) ?: $file;
    $relPath = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], strlen(realpath($rootDir) ?: $rootDir)));
    $_SERVER['SCRIPT_NAME'] = '/' . ltrim($relPath, '/');
    $_SERVER['PHP_SELF'] = '/' . ltrim($relPath, '/');
    $_SERVER['DOCUMENT_ROOT'] = realpath($rootDir) ?: $rootDir;

    // Cambiar directorio de trabajo a la raíz para includes relativos
    chdir($rootDir);

    // Cargar autoload de composer si existe
    $vendorAutoload = $rootDir . '/vendor/autoload.php';
    if (file_exists($vendorAutoload)) {
        require_once $vendorAutoload;
    }

    // Ejecutar el archivo PHP solicitado
    require $file;
    exit;
}

// 6. Si el archivo es HTML o estático y llegó hasta aquí
if (is_file($file)) {
    $mime = mime_content_type($file);
    if ($mime) {
        header("Content-Type: $mime");
    }
    readfile($file);
    exit;
}

// 7. Retornar 404 si el recurso no existe
http_response_code(404);
echo "404 Not Found - El recurso solicitado no existe.";
exit;
