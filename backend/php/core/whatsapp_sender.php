<?php
// backend/php/core/whatsapp_sender.php
// Notificaciones de WhatsApp vía Twilio REST API (sin depender del SDK).

// Este archivo no debe ser accedido directamente.
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
  http_response_code(403);
  die('Acceso denegado.');
}

if (!function_exists('dc_whatsapp_config')) {
  function dc_whatsapp_config(): array {
    return [
      'sid'   => getenv('TWILIO_ACCOUNT_SID') ?: '',
      'token' => getenv('TWILIO_AUTH_TOKEN') ?: '',
      'from'  => getenv('TWILIO_WHATSAPP_FROM') ?: 'whatsapp:+14155238886', // sandbox Twilio
    ];
  }

  function dc_whatsapp_configured(): bool {
    $c = dc_whatsapp_config();
    return $c['sid'] !== '' && $c['token'] !== '';
  }

  // Normaliza un número colombiano a E.164 (sin prefijo whatsapp)
  function dc_whatsapp_to_e164($phone): string {
    $digits = preg_replace('/\D+/', '', (string)$phone);
    if ($digits === '') return '';
    if (strlen($digits) === 10 && $digits[0] === '3') {
      return '57' . $digits; // celular Colombia
    }
    if (strlen($digits) === 12 && substr($digits, 0, 2) === '57') {
      return $digits;
    }
    if (strlen($digits) === 11 && $digits[0] === '0' && $digits[1] === '3') {
      return '57' . substr($digits, 1); // 03XXXXXXXXX
    }
    return $digits; // asumir que ya viene en E.164
  }

  // Envía un mensaje de WhatsApp. Fallo silencioso si no está configurado.
  function dc_send_whatsapp($to, string $message): array {
    $c = dc_whatsapp_config();
    if ($c['sid'] === '' || $c['token'] === '') {
      return ['ok' => false, 'error' => 'whatsapp_not_configured'];
    }
    $number = dc_whatsapp_to_e164($to);
    if ($number === '') {
      return ['ok' => false, 'error' => 'invalid_phone'];
    }
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$c['sid']}/Messages.json";
    $data = http_build_query([
      'To'   => 'whatsapp:+' . $number,
      'From' => $c['from'],
      'Body' => $message,
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $data,
      CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
      CURLOPT_USERPWD        => $c['sid'] . ':' . $c['token'],
      CURLOPT_TIMEOUT        => 20,
    ]);
    $resp     = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err !== '') {
      return ['ok' => false, 'error' => 'twilio_curl: ' . $err];
    }
    $json = json_decode((string)$resp, true);
    $ok   = ($httpCode >= 200 && $httpCode < 300) && is_array($json) && !empty($json['sid']);
    return [
      'ok'         => $ok,
      'error'      => $ok ? null : (is_array($json) && !empty($json['message']) ? $json['message'] : 'twilio_error_' . $httpCode),
      'twilio_sid' => $ok ? $json['sid'] : null,
    ];
  }

  // Notifica al administrador sobre un nuevo pedido (best-effort).
  function dc_notify_new_order(int $orderId, string $customerEmail, float $total, string $payMethod, ?string $nequiPhone = null): array {
    $admin = getenv('ADMIN_WHATSAPP') ?: '';
    if ($admin === '') {
      return ['ok' => false, 'error' => 'ADMIN_WHATSAPP not configured'];
    }
    $methodLabels = [
      'paypal'   => 'PayPal',
      'nequi'    => 'Nequi',
      'efectivo' => 'Efectivo',
      'efecty'   => 'Efectivo',
    ];
    $label = $methodLabels[strtolower((string)$payMethod)] ?? strtoupper((string)$payMethod);
    $msg = "🛒 *Nuevo pedido* #$orderId\n"
         . "Cliente: " . ($customerEmail !== '' ? $customerEmail : 'Anónimo') . "\n"
         . "Total: \$" . number_format($total, 0, ',', '.') . " COP\n"
         . "Método: $label";
    if (!empty($nequiPhone)) {
      $msg .= "\nNequi: +" . dc_whatsapp_to_e164($nequiPhone);
    }
    return dc_send_whatsapp($admin, $msg);
  }
}
?>
