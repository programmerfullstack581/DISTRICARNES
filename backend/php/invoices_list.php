<?php
header('Content-Type: application/json; charset=utf-8');
define('BYPASS_SECURITY', true);
ob_start();

try {
    require_once __DIR__ . '/conexion.php'; // Define $conexion (PDO)
    
    $stmt = $conexion->query("
        SELECT f.id_factura, f.orden_id, f.codigo_factura, f.cliente_nombre, f.cliente_email,
               f.subtotal, f.total, f.estado, f.fecha_emision, f.metodo_entrega, f.metodo_pago
        FROM facturas f
        ORDER BY f.fecha_emision DESC
        LIMIT 50
    ");
    
    $data = $stmt->fetchAll();
    
    $invoices = [];
    foreach ($data as $row) {
        $invoices[] = [
            'id' => intval($row['id_factura']),
            'invoice_code' => $row['codigo_factura'],
            'orden_id' => intval($row['orden_id']),
            'customer_name' => $row['cliente_nombre'],
            'customer_email' => $row['cliente_email'],
            'total' => floatval($row['total']),
            'status' => $row['estado'],
            'created_at' => $row['fecha_emision'],
            'delivery_method' => $row['metodo_entrega'],
            'pay_method' => $row['metodo_pago'] ?? '',
            'items' => []
        ];
    }
    
    ob_end_clean();
    echo json_encode(['ok' => true, 'invoices' => $invoices, 'count' => count($invoices)]);
    exit;
    
} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode(['ok' => true, 'invoices' => [], 'error' => $e->getMessage()]);
    exit;
}