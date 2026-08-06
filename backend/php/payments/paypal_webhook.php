<?php
// =============================================
// WEBHOOK DE PAYPAL
// Endpoint público (server-to-server). PayPal lo invoca para notificar
// eventos de pago. NO requiere sesión/CSRF (por eso se omite security.php).
//
// URL pública (configurar en el panel de PayPal > Webhooks):
//   https://districarnes-83qm.onrender.com/backend/php/payments/paypal_webhook.php
//
// Eventos manejados:
//   - PAYMENT.CAPTURE.COMPLETED  -> confirmar pago (payment_confirmed)
//   - CHECKOUT.ORDER.COMPLETED   -> confirmar pago
//   - CHECKOUT.ORDER.APPROVED    -> solo log (el pago aún no se captura)
//
// Seguridad:
//   - Verificación de firma REAL vía POST /v1/notifications/verify-webhook-signature
//     (NO se confía en los headers paypal-* porque son spoofeables).
//   - Rate limiting por IP.
//   - Idempotencia: cada evento se procesa una sola vez (tabla paypal_webhook_events).
// =============================================

define('BYPASS_SECURITY', true);

require_once __DIR__ . '/../core/paypal_config.php';
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/orders_schema.php';
require_once __DIR__ . '/../core/rate_limit.php';
require_once __DIR__ . '/../sales/sales_utils.php';

// ---------- Rate limiting por IP (evita abuso del endpoint) ----------
$rl = dc_rate_limit_consume('paypal_webhook:ip:' . dc_client_ip(), 120, 3600);
if (!$rl['allowed']) {
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode(['received' => false, 'error' => 'rate_limited']);
    exit;
}

// ---------- Lectura del cuerpo crudo ----------
$rawBody = file_get_contents('php://input');
$event = json_decode($rawBody, true);
if (!is_array($event) || empty($event['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['received' => false, 'error' => 'invalid_event']);
    exit;
}

$eventType = isset($event['event_type']) ? (string)$event['event_type'] : '';
$eventId   = (string)$event['id'];

// ---------- Verificación de firma ----------
$sigHeaders = [
    'auth_algo'         => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
    'cert_url'          => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
    'transmission_id'   => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
    'transmission_sig'  => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
    'transmission_time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
];

if (empty($sigHeaders['transmission_sig']) || empty($sigHeaders['transmission_id']) || empty($sigHeaders['transmission_time'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['received' => false, 'error' => 'missing_signature_headers']);
    exit;
}

$webhookId = defined('PAYPAL_WEBHOOK_ID') ? PAYPAL_WEBHOOK_ID : '';
if ($webhookId === '') {
    // Sin webhook_id configurado no se puede verificar. Devolvemos 5xx para que PayPal reintente.
    error_log('paypal_webhook: PAYPAL_WEBHOOK_ID no está configurado en el entorno.');
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['received' => false, 'error' => 'webhook_id_not_configured']);
    exit;
}

$verified = paypal_verify_webhook($PAYPAL_CONFIG, $PAYPAL_BASE_URL, $sigHeaders, $webhookId, $event);
if (!$verified) {
    // Firma inválida: rechazar (403). No se procesa el evento.
    error_log('paypal_webhook: firma inválida para evento ' . $eventId . ' tipo ' . $eventType);
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['received' => false, 'error' => 'signature_invalid']);
    exit;
}

// ---------- Idempotencia: registrar el evento una sola vez ----------
ensure_paypal_webhook_schema($conexion);
$eventOrderId = paypal_extract_order_id($event);

$stmtIns = $conexion->prepare(
    "INSERT INTO paypal_webhook_events (event_id, event_type, paypal_order_id, status) VALUES (?, ?, ?, 'processing')
     ON CONFLICT (event_id) DO NOTHING"
);
$stmtIns->execute([$eventId, $eventType, $eventOrderId]);
if ($stmtIns->rowCount() === 0) {
    // Evento ya procesado anteriormente (reintento de PayPal): solo acusar recibo.
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['received' => true, 'duplicate' => true, 'event_type' => $eventType]);
    exit;
}

// ---------- Procesar el evento ----------
$processed = paypal_process_event($conexion, $event, $eventType, $eventOrderId);

if (!$processed['ok']) {
    // Error de infraestructura (BD/red): devolver 5xx para que PayPal reintente.
    error_log('paypal_webhook: error procesando evento ' . $eventId . ': ' . $processed['error']);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['received' => false, 'error' => $processed['error']]);
    exit;
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'received'   => true,
    'event_type' => $eventType,
    'matched'    => $processed['matched'],
    'order_id'   => $processed['order_id'],
]);
exit;

// =============================================
// Funciones auxiliares
// =============================================

// Verifica la firma del webhook usando la API oficial de PayPal
// (POST /v1/notifications/verify-webhook-signature). Devuelve true si
// verification_status === "SUCCESS".
function paypal_verify_webhook(array $config, string $baseUrl, array $headers, string $webhookId, array $event): bool {
    $token = paypal_get_access_token($config, $baseUrl);
    if (!$token) {
        error_log('paypal_webhook: no se pudo obtener access token para verificar firma.');
        return false;
    }

    $body = [
        'auth_algo'         => $headers['auth_algo'],
        'cert_url'          => $headers['cert_url'],
        'transmission_id'   => $headers['transmission_id'],
        'transmission_sig'  => $headers['transmission_sig'],
        'transmission_time' => $headers['transmission_time'],
        'webhook_id'        => $webhookId,
        'webhook_event'     => $event,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/v1/notifications/verify-webhook-signature',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
    ]);
    $skipVerify = getenv('PAYPAL_SKIP_SSL_VERIFY') === '1';
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $skipVerify ? 0 : 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $skipVerify ? 0 : 2);

    $res = curl_exec($ch);
    if ($res === false) {
        $err = curl_error($ch);
        curl_close($ch);
        error_log('paypal_webhook: curl verify error: ' . $err);
        return false;
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);
    if ($code < 200 || $code >= 300) {
        error_log('paypal_webhook: verify API devolvió ' . $code . ': ' . substr($res, 0, 500));
        return false;
    }

    $status = $json['verification_status'] ?? '';
    if ($status !== 'SUCCESS') {
        error_log('paypal_webhook: verification_status = ' . $status);
        return false;
    }
    return true;
}

// Extrae el ID de la orden de PayPal a partir del evento. El proyecto guarda
// en orders_pg.paypal_id el ID de la orden de PayPal (v2/checkout/orders).
function paypal_extract_order_id(array $event): ?string {
    $resource = $event['resource'] ?? [];
    if (!is_array($resource)) return null;

    // Si en el futuro se envía custom_id numérico (ID local), usarlo directo.
    $customId = isset($resource['custom_id']) ? trim((string)$resource['custom_id']) : '';
    if ($customId !== '' && ctype_digit($customId)) {
        return $customId;
    }

    // CHECKOUT.ORDER.COMPLETED / CHECKOUT.ORDER.APPROVED: resource.id = order id
    if (strpos((string)($event['event_type'] ?? ''), 'CHECKOUT.ORDER.') === 0) {
        $id = $resource['id'] ?? null;
        return $id ? (string)$id : null;
    }

    // PAYMENT.CAPTURE.COMPLETED: el order id vive en supplementary_data.related_ids
    $related = $resource['supplementary_data']['related_ids'] ?? [];
    if (is_array($related) && !empty($related['order_id'])) {
        return (string)$related['order_id'];
    }

    return null;
}

// Aplica el evento al pedido local. Devuelve:
//   ok      => true/false (error de infraestructura)
//   matched => true si encontró el pedido y lo actualizó
//   order_id=> ID local del pedido (o null)
function paypal_process_event(PDO $db, array $event, string $eventType, ?string $paypalOrderId): array {
    // Eventos que no confirman pago (aprobado pero no capturado): solo log.
    if ($eventType === 'CHECKOUT.ORDER.APPROVED') {
        error_log('paypal_webhook: pedido aprobado (aún no capturado): ' . ($paypalOrderId ?? 'sin id'));
        return ['ok' => true, 'matched' => false, 'order_id' => null];
    }

    $paymentEvents = ['PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.COMPLETED'];
    if (!in_array($eventType, $paymentEvents, true)) {
        // Otros eventos (fallidos, denegados...) solo se registran para auditoría.
        error_log('paypal_webhook: evento no procesado: ' . $eventType);
        return ['ok' => true, 'matched' => false, 'order_id' => null];
    }

    if ($paypalOrderId === null || $paypalOrderId === '') {
        return ['ok' => true, 'matched' => false, 'order_id' => null];
    }

    ensure_orders_schema($db);

    // Buscar el pedido local por su paypal_id (ID de orden de PayPal)
    $stmt = $db->prepare("SELECT id, status, payment_confirmed FROM orders_pg WHERE paypal_id = ? LIMIT 1");
    $stmt->execute([$paypalOrderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!$order) {
        error_log('paypal_webhook: pedido local no encontrado para paypal_id=' . $paypalOrderId . ' (' . $eventType . ')');
        return ['ok' => true, 'matched' => false, 'order_id' => null];
    }

    $orderId    = (int)$order['id'];
    $current    = strtoupper((string)$order['status']);
    $confirmed  = (bool)$order['payment_confirmed'];

    // Idempotente: si ya está COMPLETADO y confirmado, no re-procesar.
    if ($current === 'COMPLETED' && $confirmed) {
        return ['ok' => true, 'matched' => true, 'order_id' => $orderId];
    }

    // Confirmar pago y pasar la orden a COMPLETED (operativo) al recibir el evento.
    $upd = $db->prepare(
        "UPDATE orders_pg SET status = 'COMPLETED', payment_confirmed = TRUE, payment_confirmed_at = CURRENT_TIMESTAMP WHERE id = ?"
    );
    $upd->execute([$orderId]);

    // Registrar venta (si aún no existe) y notificación.
    try {
        record_sale_for_order($db, $orderId);
    } catch (Throwable $e) {
        error_log('paypal_webhook: record_sale_for_order #' . $orderId . ': ' . $e->getMessage());
    }
    try {
        ensure_notificaciones_schema($db);
        record_notificacion(
            $db,
            'sale',
            "Pago PayPal confirmado #$orderId",
            "Webhook PayPal: " . $eventType,
            $orderId
        );
    } catch (Throwable $e) {
        error_log('paypal_webhook: notificación #' . $orderId . ': ' . $e->getMessage());
    }

    error_log('paypal_webhook: pedido #' . $orderId . ' confirmado vía ' . $eventType);
    return ['ok' => true, 'matched' => true, 'order_id' => $orderId];
}

// Crea la tabla de auditoría/idempotencia de eventos del webhook.
function ensure_paypal_webhook_schema(PDO $db): void {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS paypal_webhook_events (
                id SERIAL PRIMARY KEY,
                event_id VARCHAR(64) UNIQUE NOT NULL,
                event_type VARCHAR(64) NULL,
                paypal_order_id VARCHAR(255) NULL,
                status VARCHAR(32) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    } catch (Throwable $e) {
        error_log('paypal_webhook: ensure schema: ' . $e->getMessage());
    }
}
