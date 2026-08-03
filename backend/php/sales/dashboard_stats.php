<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/conexion.php';
require_once __DIR__ . '/../core/producto_caducidad.php';
require_once __DIR__ . '/../core/cache_respuesta.php';

// Copia fresca en caché? Evita el costo de caducidad + todos los conteos
if (cache_respuesta_probar(15, 'dash_stats')) { exit; }

// Asegurar columna de vencidos y refrescar estado antes del conteo
producto_caducidad_aplicar($conexion);

function table_exists(PDO $db, string $name): bool {
  static $cache = [];
  if (array_key_exists($name, $cache)) return $cache[$name];
  $stmt = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_name = ? LIMIT 1");
  $stmt->execute([$name]);
  $cache[$name] = (bool)$stmt->fetch();
  $stmt->closeCursor();
  return $cache[$name];
}

function safe_sum_today_sales(PDO $db): float {
  if (!table_exists($db, 'orders_pg')) return 0.0;
  $sql = "SELECT SUM(total) AS suma FROM orders_pg WHERE status = 'COMPLETED' AND DATE(created_at) = CURRENT_DATE";
  try {
      if ($res = $db->query($sql)) {
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $res->closeCursor();
        return isset($row['suma']) ? floatval($row['suma']) : 0.0;
      }
  } catch (PDOException $e) {}
  return 0.0;
}

function safe_orders_in_route(PDO $db): int {
  if (!table_exists($db, 'orders_pg')) return 0;
  // Consideramos 'PROCESSING' como en ruta/preparación
  $sql = "SELECT COUNT(*) AS cnt FROM orders_pg WHERE status = 'PROCESSING'";
  try {
      if ($res = $db->query($sql)) {
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $res->closeCursor();
        return isset($row['cnt']) ? intval($row['cnt']) : 0;
      }
  } catch (PDOException $e) {}
  return 0;
}

function detect_stock_column(PDO $db, string $table = 'producto'): ?string {
  static $cache = [];
  if (array_key_exists($table, $cache)) return $cache[$table];
  if (!table_exists($db, $table)) { $cache[$table] = null; return null; }
  $stmt = $db->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = ?");
  $stmt->execute([$table]);
  $cols = [];
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $cols[] = $r['column_name']; }
  $stmt->closeCursor();
  
  $found = null;
  foreach (['stock','existencias','cantidad','qty'] as $c) { if (in_array($c, $cols, true)) { $found = $c; break; } }
  $cache[$table] = $found;
  return $found;
}

function safe_low_stock_count(PDO $db): int {
  $tbl = 'producto';
  $stockCol = detect_stock_column($db, $tbl);
  if (!$stockCol) return 0;
  // Excluir productos vencidos del conteo de stock bajo
  $sql = "SELECT COUNT(*) AS cnt FROM \"$tbl\" WHERE \"$stockCol\" < 10 AND (estado_vencido IS NULL OR estado_vencido = FALSE)";
  try {
      if ($res = $db->query($sql)) {
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $res->closeCursor();
        return isset($row['cnt']) ? intval($row['cnt']) : 0;
      }
  } catch (PDOException $e) {}
  return 0;
}

function safe_active_customers(PDO $db): int {
  // Contar usuarios registrados que NO sean admin
  // Asumiendo que 'cliente' o rol!='admin' son los clientes
  $cnt = 0;
  
  // Opción 1: Contar desde tabla 'usuario' filtrando rol
  if (table_exists($db, 'usuario')) {
      $sql = "SELECT COUNT(*) AS cnt FROM usuario WHERE rol != 'admin'";
      try {
          if ($res = $db->query($sql)) {
            $row = $res->fetch(PDO::FETCH_ASSOC);
            $cnt += isset($row['cnt']) ? intval($row['cnt']) : 0;
          }
      } catch (PDOException $e) {}
  }
  
  // Opción 2: Si existe tabla 'cliente', sumar también (evitando duplicados si fuera posible, pero aquí sumamos bruto)
  // En este sistema parece que usuario con rol 'trabajo' o 'cliente' son los usuarios
  
  return $cnt;
}

$resp = [
  'ok' => true,
  'salesToday' => safe_sum_today_sales($conexion),
  'ordersInRoute' => safe_orders_in_route($conexion),
  'lowStockItems' => safe_low_stock_count($conexion),
  'activeCustomers' => safe_active_customers($conexion),
];
cache_respuesta_guardar($resp, 'dash_stats');
echo json_encode($resp);
exit;
?>