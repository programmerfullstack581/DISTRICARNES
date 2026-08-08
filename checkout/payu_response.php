<?php
// =============================================
// Respuesta PayU (WebCheckout) — página que ve el cliente
// al volver de PayU. SOLO muestra el resultado: NO marca la
// compra como pagada. La confirmación real la hace
// backend/php/payments/payu_confirmation.php (POST del servidor).
// =============================================
require_once __DIR__ . '/../backend/php/core/payu_config.php';
require_once __DIR__ . '/../backend/php/core/conexion.php';
require_once __DIR__ . '/../backend/php/core/orders_schema.php';

$merchantId    = isset($_GET['merchant_id']) ? trim((string)$_GET['merchant_id']) : '';
$referenceSale = isset($_GET['reference_sale']) ? trim((string)$_GET['reference_sale']) : '';
$value         = isset($_GET['value']) ? trim((string)$_GET['value']) : '';
$currency      = isset($_GET['currency']) ? trim((string)$_GET['currency']) : '';
$signature     = isset($_GET['signature']) ? trim((string)$_GET['signature']) : '';
$statePol      = isset($_GET['state_pol']) ? trim((string)$_GET['state_pol']) : '';
$transactionId = isset($_GET['transaction_id']) ? trim((string)$_GET['transaction_id']) : '';
$extra1        = isset($_GET['extra1']) ? trim((string)$_GET['extra1']) : '';

$sigValid = false;
$orderId  = intval($extra1);
$orderEmail = '';

if ($referenceSale !== '' && $value !== '' && $currency !== '' && $signature !== '') {
  $expected = payu_signature($referenceSale, $value, $currency);
  $sigValid = hash_equals($expected, $signature);
}

$status = $sigValid ? payu_state_to_status($statePol) : 'PENDING';

// Buscar datos de la orden (solo para mostrar referencia/info)
try {
  if ($referenceSale !== '') {
    ensure_orders_schema($conexion);
    $stmt = $conexion->prepare("SELECT id, user_email, user_name, total FROM orders_pg WHERE reference_code = ? LIMIT 1");
    $stmt->execute([$referenceSale]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    if ($row) {
      $orderId = intval($row['id']);
      $orderEmail = (string)($row['user_email'] ?? '');
    }
  }
} catch (Throwable $e) {
  error_log('payu_response lookup: ' . $e->getMessage());
}

$esAprobado = ($status === 'COMPLETED');
$esPendiente = ($status === 'PENDING');
$esRechazado = in_array($status, ['CANCELLED', 'ERROR'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resultado del pago | DISTRICARNES</title>
    <link rel="stylesheet" href="../static/css/base.css" />
    <link rel="stylesheet" href="../static/css/theme.css" />
    <link rel="icon" href="../assets/icon/image-removebg-preview sin fondo (1).ico" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0a0a;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #fff;
            padding: 16px;
            box-sizing: border-box;
        }
        html[data-theme="light"] body {
            background: #f5f5f5;
            color: #111;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: #171717;
            border: 1px solid #2a2a2a;
            border-radius: 18px;
            padding: 36px 28px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,.45);
            box-sizing: border-box;
        }
        html[data-theme="light"] .card {
            background: #fff;
            border-color: #e5e7eb;
            box-shadow: 0 20px 50px rgba(0,0,0,.08);
        }
        .icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        .icon.ok { background: rgba(16,185,129,.15); color: #10b981; }
        .icon.wait { background: rgba(245,158,11,.15); color: #f59e0b; }
        .icon.no { background: rgba(239,68,68,.15); color: #ef4444; }
        h1 { margin: 0 0 10px; font-size: 24px; }
        p { color: #9ca3af; margin: 6px 0; font-size: 14px; }
        html[data-theme="light"] p { color: #4b5563; }
        .total { font-size: 30px; font-weight: 800; margin: 16px 0 4px; color: #fff; }
        html[data-theme="light"] .total { color: #111; }
        .ref { font-size: 12px; color: #6b7280; margin-bottom: 22px; }
        .btn {
            display: block;
            width: 100%;
            padding: 13px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            margin-top: 10px;
            box-sizing: border-box;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background: #ff0000; color: #fff; }
        .btn-primary:hover { background: #cc0000; }
        .btn-ghost { background: transparent; color: #d1d5db; border: 1px solid #3a3a3a; }
        html[data-theme="light"] .btn-ghost { color: #374151; border-color: #e5e7eb; }
        .btn-ghost:hover { border-color: #6b7280; }
        .logo img { height: 42px; margin-bottom: 24px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <img src="../assets/icon/LOGO-DISTRICARNES.png" alt="DISTRICARNES" onerror="this.style.display='none';" />
        </div>
        <?php if ($esAprobado): ?>
            <div class="icon ok"><i class="bi bi-check-lg"></i></div>
            <h1>¡Pago aprobado!</h1>
            <p>Tu pedido fue confirmado. Recibirás tu factura por correo.</p>
            <div class="total">$<?= number_format((float)$value ?: 0, 0, ',', '.') ?></div>
            <div class="ref">Referencia: <?= htmlspecialchars($referenceSale) ?></div>
            <a class="btn btn-primary" href="../backend/php/orders/order_invoice.php?order_id=<?= intval($orderId) ?>&print=1">Ver factura</a>
            <a class="btn btn-ghost" href="../index.php">Volver al inicio</a>
        <?php elseif ($esPendiente): ?>
            <div class="icon wait"><i class="bi bi-hourglass-split"></i></div>
            <h1>Pago pendiente</h1>
            <p>Tu pago está siendo procesado. Te avisaremos cuando se confirme.</p>
            <div class="total">$<?= number_format((float)$value ?: 0, 0, ',', '.') ?></div>
            <div class="ref">Referencia: <?= htmlspecialchars($referenceSale) ?></div>
            <a class="btn btn-primary" href="../index.php">Volver al inicio</a>
            <a class="btn btn-ghost" href="../historial.php">Ver mis pedidos</a>
        <?php elseif ($esRechazado): ?>
            <div class="icon no"><i class="bi bi-x-lg"></i></div>
            <h1>Pago no completado</h1>
            <p>La transacción no fue aprobada. Puedes intentarlo nuevamente.</p>
            <a class="btn btn-primary" href="../carrito-de-compras/index.php">Volver al carrito</a>
            <a class="btn btn-ghost" href="../index.php">Volver al inicio</a>
        <?php else: ?>
            <div class="icon wait"><i class="bi bi-question-lg"></i></div>
            <h1>No se pudo verificar el pago</h1>
            <p>Si ya realizaste el pago, recibirás la confirmación por correo.</p>
            <a class="btn btn-primary" href="../index.php">Volver al inicio</a>
        <?php endif; ?>
    </div>

    <?php if ($esAprobado && $orderId > 0): ?>
    <script src="../static/js/csrf_client.js"></script>
    <script>
        (function () {
            // Limpiar carrito local (best-effort)
            try {
                var keys = Object.keys(localStorage);
                for (var i = 0; i < keys.length; i++) {
                    if (keys[i].indexOf('cart_items') === 0) {
                        localStorage.removeItem(keys[i]);
                    }
                }
                window.dispatchEvent(new CustomEvent('cart:updated', { detail: { items: [] } }));
            } catch (e) {}
            // Enviar factura al correo (best-effort)
            var email = <?= json_encode($orderEmail) ?>;
            if (email) {
                try {
                    window.dcFetchJson('../backend/php/orders/send_invoice_email.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ order_id: <?= intval($orderId) ?>, to: email })
                    }).catch(function(){});
                } catch (e) {}
            }
        })();
    </script>
    <?php endif; ?>
</body>
</html>
