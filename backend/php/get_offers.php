<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/conexion.php';

// Normaliza el campo productos_json para que siempre regrese un array de IDs
function normalize_products_field($raw){
  if ($raw === null) return [];
  $s = trim((string)$raw);
  if ($s === '') return [];
  // Intentar JSON primero
  $decoded = json_decode($s, true);
  if (is_array($decoded)) {
    $out = [];
    foreach ($decoded as $v) {
      $t = trim((string)$v);
      if ($t !== '') { $out[] = $t; }
    }
    return $out;
  }
  // Fallback: CSV (coma, punto y coma o barra vertical)
  $parts = preg_split('/[,;|]/', $s);
  $out = [];
  foreach ($parts as $p) {
    $t = trim((string)$p);
    if ($t !== '') { $out[] = $t; }
  }
  return $out;
}

// Asegura que exista la tabla 'ofertas' (PostgreSQL)
function ensure_offers_table(PDO $db): void {
  $sql = "CREATE TABLE IF NOT EXISTS ofertas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    tipo VARCHAR(20) NOT NULL DEFAULT 'percentage',
    valor_descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_inicio TIMESTAMP NULL,
    fecha_fin TIMESTAMP NULL,
    limite_usos INT NULL,
    estado VARCHAR(16) NOT NULL DEFAULT 'inactive',
    productos_json TEXT NULL,
    imagen VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )";
  $db->exec($sql);
}

// Asegura que la columna 'imagen' exista en tablas ya creadas previamente
function ensure_offer_image_column(PDO $db): void {
  $has = false;
  $stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'ofertas' AND column_name = 'imagen'");
  if ($stmt && $stmt->fetch()) { $has = true; }
  if ($stmt) $stmt->closeCursor();
  if (!$has) { @$db->exec("ALTER TABLE ofertas ADD COLUMN imagen VARCHAR(255) NULL"); }
}

ensure_offers_table($conexion);
ensure_offer_image_column($conexion);

// Desactivar automáticamente promociones expiradas y activar programadas
$conexion->exec("UPDATE ofertas SET estado = 'inactive' WHERE estado = 'active' AND fecha_fin IS NOT NULL AND fecha_fin < CURRENT_TIMESTAMP");
$conexion->exec("UPDATE ofertas SET estado = 'active' WHERE estado = 'scheduled' AND fecha_inicio IS NOT NULL AND fecha_inicio <= CURRENT_TIMESTAMP");

$status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : 'active';
$onlyActive = isset($_GET['only_active']) ? (trim($_GET['only_active']) !== '' ? filter_var($_GET['only_active'], FILTER_VALIDATE_BOOLEAN) : null) : null;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Construir consulta seleccionando solo ofertas disponibles actualmente
$query = "SELECT id, nombre, descripcion, tipo, valor_descuento, fecha_inicio, fecha_fin, limite_usos, estado, productos_json, imagen, created_at FROM ofertas";
$where = [];
$params = [];

// Filtro por estado
if ($onlyActive === null) {
  // Si no se pasa only_active, usar 'status' (por defecto 'active')
  if ($status !== '') { $where[] = 'estado = ?'; $params[] = $status; }
} else if ($onlyActive) {
  $where[] = "estado = 'active'";
}

// Vigencia por fecha (si están dentro del rango o sin límites)
// NOW() en PostgreSQL regresa la fecha actual del servidor.
$where[] = "( (fecha_inicio IS NULL OR fecha_inicio <= CURRENT_TIMESTAMP) AND (fecha_fin IS NULL OR fecha_fin >= CURRENT_TIMESTAMP) )";

// Búsqueda por texto opcional
if ($q !== '') { $where[] = '(nombre ILIKE ? OR descripcion ILIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }

if (!empty($where)) { $query .= ' WHERE ' . implode(' AND ', $where); }
$query .= ' ORDER BY created_at DESC';

$stmt = $conexion->prepare($query);
if (!$stmt) { echo json_encode(['ok' => false, 'error' => 'Error preparando consulta']); exit; }
$stmt->execute($params);

$offers = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  // Normalizar productos
  $products = normalize_products_field(isset($row['productos_json']) ? $row['productos_json'] : null);

  $offers[] = [
    'id' => intval($row['id']),
    'title' => $row['nombre'],
    'description' => $row['descripcion'],
    'type' => $row['tipo'],
    'discount_value' => floatval($row['valor_descuento']),
    'start_date' => $row['fecha_inicio'],
    'end_date' => $row['fecha_fin'],
    'usage_limit' => isset($row['limite_usos']) ? intval($row['limite_usos']) : null,
    'products' => $products,
    'status' => $row['estado'],
    'image' => (isset($row['imagen']) && trim((string)$row['imagen']) !== '' ? $row['imagen'] : null),
    'created_at' => $row['created_at'],
  ];
}
$stmt->closeCursor();

echo json_encode(['ok' => true, 'offers' => $offers]);
exit;
?>