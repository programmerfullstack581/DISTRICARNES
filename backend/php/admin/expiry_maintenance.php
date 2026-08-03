<?php
// ENDPOINT TEMPORAL DE MANTENIMIENTO
// - action=renovar_vencidos: renueva la fecha_caducidad de los productos vencidos a hoy+30
//   (marca vencido = FALSE para reanudar su venta). Los que NO están vencidos quedan intactos.
// - action=estado: devuelve conteo de productos vencidos/no vencidos (sin modificar nada).
// DESPUÉS DE USARLO, ELIMINAR este archivo y su regla en .htaccess.

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/producto_caducidad.php';

producto_caducidad_asegurar_esquema($conexion);
producto_caducidad_marcar_vencidos($conexion);

function countAll(PDO $db): array {
  $t = $db->query("SELECT COUNT(*) c FROM producto")->fetch(PDO::FETCH_ASSOC)['c'];
  $v = $db->query("SELECT COUNT(*) c FROM producto WHERE estado_vencido = TRUE")->fetch(PDO::FETCH_ASSOC)['c'];
  return ['total' => (int)$t, 'vencidos' => (int)$v, 'no_vencidos' => (int)$t - (int)$v];
}

$action = $_GET['action'] ?? 'estado';

if ($action === 'renovar_vencidos') {
  $stmt = $conexion->prepare("UPDATE producto SET fecha_caducidad = CURRENT_DATE + INTERVAL '30 days', estado_vencido = FALSE WHERE estado_vencido = TRUE");
  $stmt->execute();
  $afectados = $stmt->rowCount();
  $stmt->closeCursor();
  echo json_encode(['ok' => true, 'renovados' => $afectados, 'estado' => countAll($conexion)]);
  exit;
}

echo json_encode(['ok' => true, 'estado' => countAll($conexion)]);
