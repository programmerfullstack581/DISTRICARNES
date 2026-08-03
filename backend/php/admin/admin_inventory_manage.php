<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/producto_lotes.php';
require_once __DIR__ . '/../core/cache_respuesta.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

$action = $_POST['action'] ?? '';
if ($action !== 'restock') {
  echo json_encode(['success' => false, 'message' => 'Acción inválida']);
  exit;
}

// Campos desde el formulario de inventario
$productId = $_POST['product_id'] ?? null;
$addQuantity = isset($_POST['add_quantity']) ? (int)$_POST['add_quantity'] : null;
$notes = $_POST['notes'] ?? null;

if (!$productId || $addQuantity === null || $addQuantity < 1) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos para reabastecer']);
  exit;
}

// Detectar columnas de la tabla producto
function getColumns(PDO $db, string $table): array {
  $cols = [];
  $stmt = $db->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = ?");
  $stmt->execute([$table]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[] = $row['column_name'];
  }
  $stmt->closeCursor();
  return $cols;
}

function findIdColumn(array $cols): ?string {
  foreach (['id', 'id_producto', 'producto_id', 'idProduct'] as $c) {
    if (in_array($c, $cols, true)) return $c;
  }
  return null;
}

$table = 'producto';
$columns = getColumns($conexion, $table);
$idCol = findIdColumn($columns);
$stockCol = in_array('stock', $columns, true) ? 'stock' : null;

if (!$idCol || !$stockCol) {
  echo json_encode(['success' => false, 'message' => 'Estructura de tabla inesperada (id/stock)']);
  exit;
}

// Nueva fecha de caducidad (opcional): al actualizarla se reanuda la venta
$newExpiry = trim($_POST['new_expiry'] ?? '');
$hasNewExpiry = false;
if ($newExpiry !== '') {
  $d = DateTime::createFromFormat('Y-m-d', $newExpiry);
  if (!$d || $d->format('Y-m-d') !== $newExpiry) {
    echo json_encode(['success' => false, 'message' => 'Fecha de caducidad inválida']);
    exit;
  }
  $hasNewExpiry = true;
}

// Precio de compra del lote (opcional): si no se envía, se usa el del producto
$newPrecioCompra = ($_POST['new_precio_compra'] ?? '');
if (is_string($newPrecioCompra)) $newPrecioCompra = trim($newPrecioCompra);

// Obtener stock actual + fecha de caducidad y precio de compra actuales
$extraSelect = '';
foreach (['fecha_caducidad', 'precio_compra_lote'] as $c) {
  if (in_array($c, $columns, true)) { $extraSelect .= ', "' . $c . '"'; }
}
$stmt = $conexion->prepare("SELECT \"$stockCol\"$extraSelect FROM \"$table\" WHERE \"$idCol\" = ? LIMIT 1");
$stmt->execute([$productId]);
// fetch directly
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
  $stmt->closeCursor();
  exit;
}
$currentStock = (int)$row[$stockCol];
$stmt->closeCursor();

$newStock = $currentStock + $addQuantity;

// Registrar el lote nuevo (cada compra es un lote con su propia fecha)
$loteFecha = $hasNewExpiry ? $newExpiry : (isset($row['fecha_caducidad']) && $row['fecha_caducidad'] !== null ? $row['fecha_caducidad'] : null);
$lotePrecio = ($newPrecioCompra !== '' && $newPrecioCompra !== null) ? $newPrecioCompra : (isset($row['precio_compra_lote']) ? $row['precio_compra_lote'] : null);
$loteRegistrado = producto_lotes_registrar($conexion, $productId, $addQuantity, $loteFecha, $lotePrecio, null, $notes);

// Actualizar stock (y opcionalmente fecha de caducidad + revertir vencido)
if ($hasNewExpiry) {
  $stmt2 = $conexion->prepare("UPDATE \"$table\" SET \"$stockCol\" = ?, fecha_caducidad = ?, estado_vencido = FALSE WHERE \"$idCol\" = ?");
  $ok = $stmt2->execute([$newStock, $newExpiry, $productId]);
} else {
  $stmt2 = $conexion->prepare("UPDATE \"$table\" SET \"$stockCol\" = ? WHERE \"$idCol\" = ?");
  $ok = $stmt2->execute([$newStock, $productId]);
}
$stmt2->closeCursor();

if ($ok) {
  cache_respuesta_invalidar();
  echo json_encode([
    'success' => true,
    'message' => 'Stock actualizado correctamente',
    'new_stock' => $newStock,
    'lote' => $loteRegistrado
  ]);
} else {
  echo json_encode(['success' => false, 'message' => 'No fue posible actualizar el stock']);
}

// $conexion->close();
?>