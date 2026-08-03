<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/admin_auth.php';
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/orders_schema.php';
require_once __DIR__ . '/../core/producto_caducidad.php';
require_once __DIR__ . '/../core/cache_respuesta.php';

if (cache_respuesta_probar(15, 'notifications')) { exit; }

producto_caducidad_aplicar($conexion);
ensure_orders_schema($conexion);
ensure_notificaciones_schema($conexion);

function format_order_event(array $row): array {
    $id = intval($row['id']);
    $status = strtoupper((string)$row['status']);
    $user = isset($row['user_name']) ? $row['user_name'] : 'Cliente';
    $created = $row['created_at'];
    $total = isset($row['total']) ? floatval($row['total']) : 0.0;

    $type = 'order';
    $title = '';
    $message = '';

    if ($status === 'PENDING') {
        $type = 'order';
        $title = "Nuevo pedido #$id";
        $message = "$user realizó un pedido";
    } else if ($status === 'PROCESSING') {
        $type = 'order';
        $title = "Pedido en proceso #$id";
        $message = "El pedido está siendo preparado/envíado";
    } else if ($status === 'COMPLETED') {
        $type = 'sale';
        $title = "Orden completada #$id";
        $message = "Venta registrada: $" . number_format($total, 2);
    } else if ($status === 'CANCELLED') {
        $type = 'order';
        $title = "Pedido cancelado #$id";
        $message = "El pedido fue cancelado";
    } else {
        $type = 'order';
        $title = "Actualización de pedido #$id";
        $message = "Estado: $status";
    }

    return [
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'created_at' => $created,
        'link' => "./admin_orders.html?orderId=$id"
    ];
}

function format_user_event(array $row): array {
    $name = isset($row['nombres_completos']) ? $row['nombres_completos'] : 'Usuario';
    $created = $row['created_at'];
    return [
        'type' => 'user',
        'title' => 'Nuevo usuario registrado',
        'message' => "$name se ha unido al sistema",
        'created_at' => $created,
        'link' => './admin_users.html'
    ];
}

$notifications = [];

try {
    // 1. Sincronizar órdenes recientes que aún no tienen notificación persistida
    $syncOrders = table_exists($conexion, 'orders_pg');
    if ($syncOrders) {
        $stmt = $conexion->prepare("
            SELECT o.id, o.user_name, o.user_email, o.status, o.total, o.created_at, o.paypal_id
            FROM orders_pg o
            LEFT JOIN notificaciones_admin n ON n.order_id = o.id AND n.type IN ('order', 'sale')
            WHERE o.created_at >= (NOW() - INTERVAL '24 hours')
              AND n.id IS NULL
            ORDER BY o.created_at DESC
            LIMIT 20
        ");
        $stmt->execute();
        $syncedCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ev = format_order_event($row);
            record_notificacion(
                $conexion,
                $ev['type'],
                $ev['title'],
                $ev['message'],
                intval($row['id']),
                $row['paypal_id']
            );
            $syncedCount++;
        }
        $stmt->closeCursor();
        if ($syncedCount > 0) {
            cache_respuesta_invalidar();
        }
    }

    // 2. Sincronizar nuevos usuarios que aún no tienen notificación
    $syncUsers = table_exists($conexion, 'usuario');
    if ($syncUsers) {
        $userDateCol = col_exists($conexion, 'usuario', 'created_at') ? 'created_at' : (
            col_exists($conexion, 'usuario', 'fecha_registro') ? 'fecha_registro' :
            col_exists($conexion, 'usuario', 'created_at') ? 'created_at' : 'created_at'
        );

        $stmt = $conexion->prepare("
            SELECT u.nombres_completos, u.$userDateCol as created_at, u.id_usuario
            FROM usuario u
            LEFT JOIN notificaciones_admin n ON n.ref_id = u.id_usuario::text AND n.type = 'user'
            WHERE u.rol != 'admin'
              AND u.$userDateCol >= (NOW() - INTERVAL '24 hours')
              AND n.id IS NULL
            ORDER BY u.$userDateCol DESC
            LIMIT 10
        ");
        $stmt->execute();
        $syncedUserCount = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ev = format_user_event($row);
            record_notificacion(
                $conexion,
                'user',
                $ev['title'],
                $ev['message'],
                null,
                (string)($row['id_usuario'] ?? '')
            );
            $syncedUserCount++;
        }
        $stmt->closeCursor();
        if ($syncedUserCount > 0) {
            cache_respuesta_invalidar();
        }
    }

    // 3. Alerta de stock bajo (computado en tiempo real)
    if (table_exists($conexion, 'producto')) {
        $stockCol = col_exists($conexion, 'producto', 'stock') ? 'stock' : (
            col_exists($conexion, 'producto', 'existencias') ? 'existencias' :
            col_exists($conexion, 'producto', 'cantidad') ? 'cantidad' : null
        );
        if ($stockCol) {
            $stmt = $conexion->prepare(
                "SELECT COUNT(*) AS cnt FROM producto WHERE \"$stockCol\" < 10 AND (estado_vencido IS NULL OR estado_vencido = FALSE)"
            );
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cnt = isset($row['cnt']) ? intval($row['cnt']) : 0;
            if ($cnt > 0) {
                $stmtUpd = $conexion->prepare("SELECT id FROM notificaciones_admin WHERE type = 'inventory' AND title = 'Alerta de stock bajo' ORDER BY created_at DESC LIMIT 1");
                $stmtUpd->execute();
                $existsInv = $stmtUpd->fetch();
                $stmtUpd->closeCursor();
                if (!$existsInv) {
                    record_notificacion(
                        $conexion,
                        'inventory',
                        'Alerta de stock bajo',
                        "$cnt productos con stock crítico (<10)",
                        null
                    );
                    cache_respuesta_invalidar();
                }
            }
        }
    }

    // Re-read from DB (incluye notificaciones de stock y las sincronizadas arriba)
    $stmt = $conexion->prepare("
        SELECT id, type, title, message, order_id, ref_id, leida, created_at
        FROM notificaciones_admin
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute();
    $notifications = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'id' => intval($row['id']),
            'type' => $row['type'],
            'title' => $row['title'],
            'message' => $row['message'],
            'order_id' => $row['order_id'] ? intval($row['order_id']) : null,
            'created_at' => $row['created_at'],
            'leida' => (bool)$row['leida'],
            'link' => ($row['order_id'] ? "./admin_orders.html?orderId=" . intval($row['order_id']) : './admin_orders.html')
        ];
    }
    $stmt->closeCursor();

    // Contar no leídas (última hora)
    $unread = 0;
    $threshold = strtotime('-60 minutes');
    foreach ($notifications as $n) {
        $ts = strtotime($n['created_at']);
        if ($ts !== false && $ts >= $threshold) { $unread++; }
    }

    $resp = ['ok' => true, 'notifications' => $notifications, 'unread_count' => $unread];
    cache_respuesta_guardar($resp, 'notifications');
    echo json_encode($resp);

} catch (PDOException $e) {
    error_log('notifications_list.php: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error al consultar notificaciones']);
}
exit;
?>
