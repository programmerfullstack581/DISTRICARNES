<?php
// Configuración de integración con Factus (Sandbox)
// Ajusta estos valores según tus credenciales y entorno

// Endpoint base (Sandbox)
define('FACTUS_BASE_URL', getenv('FACTUS_BASE_URL') ?: 'https://api-sandbox.factus.com.co');

// Credenciales OAuth (cliente)
define('FACTUS_CLIENT_ID', getenv('FACTUS_CLIENT_ID') ?: '');
define('FACTUS_CLIENT_SECRET', getenv('FACTUS_CLIENT_SECRET') ?: '');

// Usuario sandbox (si el flujo de autenticación lo requiere)
define('FACTUS_USERNAME', getenv('FACTUS_USERNAME') ?: 'sandbox@factus.com.co');
define('FACTUS_PASSWORD', getenv('FACTUS_PASSWORD') ?: '');

// Rutas de API (ajusta conforme a la documentación oficial)
define('FACTUS_OAUTH_TOKEN_PATH', getenv('FACTUS_OAUTH_TOKEN_PATH') ?: '/oauth/token');
define('FACTUS_INVOICE_CREATE_PATH', getenv('FACTUS_INVOICE_CREATE_PATH') ?: '/v1/invoices');

// Datos de la empresa emisora (deben coincidir con la configuración de tu cuenta Factus)
define('FACTUS_COMPANY_NAME', 'DistriCarnes Hermanos Navarro');
define('FACTUS_COMPANY_NIT', '900000000-0');
define('FACTUS_COMPANY_EMAIL', 'districarneshermanosnavarro@gmail.com');
define('FACTUS_COMPANY_PHONE', '+57 301 521 0177');
define('FACTUS_COMPANY_ADDRESS', 'OLAYA HERRERA, Cartagena de Indias');

// Moneda por defecto
define('FACTUS_CURRENCY_DEFAULT', 'COP');

?>
