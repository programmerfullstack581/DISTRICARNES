<?php

// 1. Obtener la URI de la petición y normalizarla
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = explode('?', $uri)[0]; // Remover query string para la verificación
$uri = ltrim($uri, '/');

if ($uri === '') {
    $uri = 'index.php';
}

// 2. Seguridad - Emulación de las reglas de Apache .htaccess
// 2a. Bloquear acceso a archivos ocultos (que empiezan con .)
if (str_starts_with(basename($uri), '.')) {
    http_response_code(403);
    echo "403 Forbidden - Acceso Denegado";
    exit;
}

// 2b. Bloquear archivos sensibles basados en extensiones
$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
$blockedExtensions = ['env', 'json', 'md', 'sql', 'log', 'txt', 'pem', 'key', 'yml', 'yaml', 'xml'];
if (in_array($ext, $blockedExtensions, true)) {
    http_response_code(403);
    echo "403 Forbidden - Acceso Denegado (Extensión Protegida)";
    exit;
}

// 2c. Bloquear acceso directo a archivos clave de conexión y configuración
$filename = basename($uri);
if (in_array($filename, ['conexion.php', 'config.php', 'security.php'], true)) {
    http_response_code(403);
    echo "403 Forbidden - Acceso Denegado (Archivo Protegido)";
    exit;
}

// 2d. Bloquear todo el directorio de base de datos
if (str_starts_with($uri, 'db/') || $uri === 'db') {
    http_response_code(403);
    echo "403 Forbidden - Directorio de BD Protegido";
    exit;
}

// 2e. Bloquear acceso a archivos PHP dentro de config/
if (str_starts_with($uri, 'config/')) {
    http_response_code(403);
    echo "403 Forbidden - Directorio de Configuración Protegido";
    exit;
}

// 2f. Bloquear acceso a backend/php/.*.php excepto APIs públicas (lista blanca)
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
        echo "403 Forbidden - Endpoint Interno";
        exit;
    }
}

// 2g. Bloquear acceso a backend/api/.*.php excepto chat.php
if (preg_match('/^backend\/api\/(.*)\.php$/i', $uri, $matches)) {
    $subpath = $matches[1];
    if ($subpath !== 'chat') {
        http_response_code(403);
        echo "403 Forbidden - API Interna";
        exit;
    }
}

// 3. Resolver la ruta del archivo correspondiente en el proyecto
$file = dirname(__DIR__) . '/' . $uri;

// Si la ruta solicitada es un directorio, buscar index.php dentro de él
if (is_dir($file)) {
    $file = rtrim($file, '/') . '/index.php';
    $uri = rtrim($uri, '/') . '/index.php';
}

// 4. Si el archivo es PHP y existe, ejecutarlo configurando $_SERVER
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    // Configurar adecuadamente las variables del servidor para emular ejecución nativa
    $_SERVER['SCRIPT_FILENAME'] = realpath($file);
    $_SERVER['SCRIPT_NAME'] = '/' . ltrim($uri, '/');
    $_SERVER['PHP_SELF'] = '/' . ltrim($uri, '/');
    
    // Cargar el archivo PHP
    require $file;
    exit;
}

// 5. Retornar 404 si el archivo no existe
http_response_code(404);
echo "404 Not Found - El recurso solicitado no existe.";
exit;
