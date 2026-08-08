<?php
// =============================================
// Configuración PayU — WebCheckout Colombia
// =============================================
// Las credenciales SIEMPRE se leen de variables de entorno
// (configuradas en Render), NUNCA deben ir en el código fuente.
//   PAYU_MERCHANT_ID   -> Merchant ID del panel de PayU
//   PAYU_ACCOUNT_ID    -> Account ID del panel de PayU
//   PAYU_API_KEY       -> ApiKey del panel de PayU (NO exponer en el frontend)
//   PAYU_ENV           -> 'sandbox' (default) o 'live' (producción)
//   PAYU_CURRENCY      -> 'COP' (default)
//   PAYU_TAX           -> IVA a declarar a PayU (default 0 => IVA incluido)
//   PAYU_TAX_RETURN_BASE -> Base imponible declarada (default 0)
//   PAYU_BASE_URL      -> (opcional) override de la URL pública de la app

if (!defined('PAYU_MERCHANT_ID')) define('PAYU_MERCHANT_ID', getenv('PAYU_MERCHANT_ID') ?: '');
if (!defined('PAYU_ACCOUNT_ID'))  define('PAYU_ACCOUNT_ID',  getenv('PAYU_ACCOUNT_ID')  ?: '');
if (!defined('PAYU_API_KEY'))     define('PAYU_API_KEY',     getenv('PAYU_API_KEY')     ?: '');
if (!defined('PAYU_ENV'))         define('PAYU_ENV',         getenv('PAYU_ENV') ?: 'sandbox'); // 'live' para producción
if (!defined('PAYU_CURRENCY'))    define('PAYU_CURRENCY',    getenv('PAYU_CURRENCY') ?: 'COP');
if (!defined('PAYU_TAX'))         define('PAYU_TAX',         getenv('PAYU_TAX') ?: '0');
if (!defined('PAYU_TAX_RETURN_BASE')) define('PAYU_TAX_RETURN_BASE', getenv('PAYU_TAX_RETURN_BASE') ?: '0');

// ¿Está configurado el checkout de PayU?
function payu_is_configured(): bool {
  return PAYU_MERCHANT_ID !== '' && PAYU_ACCOUNT_ID !== '' && PAYU_API_KEY !== '';
}

// URL pública de la aplicación (para responseUrl / confirmationUrl)
function payu_base_url(): string {
  $env = getenv('PAYU_BASE_URL');
  if ($env) return rtrim($env, '/');
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return ($https ? 'https://' : 'http://') . $host;
}

// URL del checkout de PayU (sandbox o producción)
function payu_checkout_url(): string {
  return PAYU_ENV === 'live'
    ? 'https://checkout.payulatam.com/pp-pweb-checkout/'
    : 'https://sandbox.checkout.payulatam.com/pp-pweb-checkout/';
}

// Firma WebCheckout: md5(ApiKey~merchantId~referenceCode~amount~currency)
function payu_signature(string $referenceCode, string $amount, string $currency): string {
  return md5(PAYU_API_KEY . '~' . PAYU_MERCHANT_ID . '~' . $referenceCode . '~' . $amount . '~' . $currency);
}

// Mapea el state_pol de PayU a un estado interno
function payu_state_to_status($statePol): string {
  switch (intval($statePol)) {
    case 4:  return 'COMPLETED'; // Aprobada
    case 5:  return 'CANCELLED'; // Expirada
    case 6:  return 'CANCELLED'; // Rechazada
    case 14: return 'CANCELLED'; // Reversada / Anulada
    case 104: return 'ERROR';
    case 7:
    case 12:
    case 999: return 'PENDING';
    default: return 'PENDING';
  }
}

function json_response($data, $status = 200){
  http_response_code($status);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

function read_json_input(){
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
