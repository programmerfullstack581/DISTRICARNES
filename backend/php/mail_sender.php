<?php
// backend/php/mail_sender.php

// Este archivo no debe ser accedido directamente.
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
  http_response_code(403);
  die('Acceso denegado.');
}

require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/smtp_mailer.php';

/**
 * Determina el proveedor de correo a utilizar basado en la configuración disponible.
 * Prioriza proveedores de API (Brevo, Resend, SendGrid) si sus claves están definidas.
 * MAIL_PROVIDER puede forzar un proveedor específico.
 * Cae a SMTP si no hay otra opción o si la opción forzada no está configurada.
 * 
 * @return string El proveedor a usar ('http_brevo', 'http_resend', 'http_sendgrid', 'smtp').
 */
function dc_mail_provider(): string {
  $prov = null;

  // 1. Detección automática de API Keys
  if (defined('BREVO_API_KEY') && BREVO_API_KEY !== '') $prov = 'http_brevo';
  elseif (defined('RESEND_API_KEY') && RESEND_API_KEY !== '') $prov = 'http_resend';
  elseif (defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') $prov = 'http_sendgrid';

  // 2. Variable de entorno para forzar un proveedor
  if (defined('MAIL_PROVIDER')) {
    $forced = strtolower(trim(MAIL_PROVIDER));
    if ($forced === 'http_brevo' && defined('BREVO_API_KEY') && BREVO_API_KEY !== '') $prov = 'http_brevo';
    elseif ($forced === 'http_resend' && defined('RESEND_API_KEY') && RESEND_API_KEY !== '') $prov = 'http_resend';
    elseif ($forced === 'http_sendgrid' && defined('SENDGRID_API_KEY') && SENDGRID_API_KEY !== '') $prov = 'http_sendgrid';
    elseif ($forced === 'smtp') $prov = 'smtp';
  }
  
  // 3. Fallback a SMTP si no se ha determinado nada
  if (!$prov) $prov = 'smtp';

  return $prov;
}

/**
 * Envía un correo electrónico utilizando el proveedor determinado por dc_mail_provider().
 * Centraliza la lógica de envío y la validación de configuración.
 *
 * @param string $to El destinatario.
 * @param string $subject El asunto.
 * @param string $body El cuerpo del mensaje (HTML o texto).
 * @param string $contentType El tipo de contenido ('text/html' o 'text/plain').
 * @return array ['ok' => bool, 'error' => ?string]
 */
function dc_send_mail(string $to, string $subject, string $body, string $contentType = 'text/html'): array {
  $prov = dc_mail_provider();
  
  $mail_from = defined('MAIL_FROM') ? MAIL_FROM : '';
  $mail_from_name = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : '';

  if ($mail_from === '') {
    return ['ok' => false, 'error' => 'mail_not_configured: MAIL_FROM no está definido.'];
  }

  if ($prov === 'http_brevo') {
    if (!defined('BREVO_API_KEY') || BREVO_API_KEY === '') return ['ok' => false, 'error' => 'mail_not_configured: BREVO_API_KEY no está definida para el proveedor Brevo.'];
    return http_send_mail($to, $subject, $body, $mail_from, $mail_from_name, [ 'provider' => 'brevo', 'api_key' => BREVO_API_KEY ], $contentType);
  }
  if ($prov === 'http_resend') {
    if (!defined('RESEND_API_KEY') || RESEND_API_KEY === '') return ['ok' => false, 'error' => 'mail_not_configured: RESEND_API_KEY no está definida para el proveedor Resend.'];
    return http_send_mail($to, $subject, $body, $mail_from, $mail_from_name, [ 'provider' => 'resend', 'api_key' => RESEND_API_KEY ], $contentType);
  }
  if ($prov === 'http_sendgrid') {
    if (!defined('SENDGRID_API_KEY') || SENDGRID_API_KEY === '') return ['ok' => false, 'error' => 'mail_not_configured: SENDGRID_API_KEY no está definida para el proveedor SendGrid.'];
    return http_send_mail($to, $subject, $body, $mail_from, $mail_from_name, [ 'provider' => 'sendgrid', 'api_key' => SENDGRID_API_KEY ], $contentType);
  }

  // Fallback a SMTP
  $smtp_host = defined('SMTP_HOST') ? SMTP_HOST : '';
  $smtp_port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
  $smtp_secure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
  $smtp_user = defined('SMTP_USER') ? SMTP_USER : '';
  $smtp_pass = defined('SMTP_PASS') ? SMTP_PASS : '';

  $placeholderPasses = ['tu_contrasena_de_aplicacion', 'APP_PASSWORD_AQUI', ''];
  if ($smtp_host === '' || $smtp_user === '' || in_array($smtp_pass, $placeholderPasses, true)) {
    return ['ok' => false, 'error' => 'mail_not_configured: La configuración SMTP está incompleta. Revisa SMTP_HOST, SMTP_USER y SMTP_PASS.'];
  }
  if ($smtp_host === 'smtp.gmail.com' && !preg_match('/^[a-z]{16}$/', $smtp_pass)) {
    // Si no es un app password de 16 caracteres, intentamos de todos modos pero avisamos en el log
    error_log('[mail_sender] Advertencia: SMTP_PASS no parece ser una Contraseña de aplicación de Gmail (16 letras minúsculas).');
  }

  return smtp_send_mail($to, $subject, $body, $mail_from, $mail_from_name, [
    'host' => $smtp_host, 'port' => $smtp_port, 'secure' => $smtp_secure, 'user' => $smtp_user, 'pass' => $smtp_pass
  ], $contentType);
}
?>