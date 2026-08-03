<?php
// =============================================
// Registro de lotes por producto
// Modelo: "solo registrar lotes" — las ventas descontan del stock general
// del producto; cada lote guarda su cantidad, fecha de caducidad y precio.
// Requiere que $conexion (PDO) ya esté definido.
// =============================================

if (!function_exists('producto_lotes_asegurar_esquema')) {
  function producto_lotes_asegurar_esquema(PDO $db): void {
    static $done = false;
    if ($done) return;
    try {
      $stmt = $db->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'lotes'");
      $exists = $stmt->fetch() !== false;
      $stmt->closeCursor();
      if (!$exists) {
        $db->exec("CREATE TABLE IF NOT EXISTS lotes (
          id_lote serial PRIMARY KEY,
          id_producto integer NOT NULL,
          numero_lote character varying(50),
          fecha_caducidad date NOT NULL,
          precio_compra_lote numeric(10,2) NOT NULL DEFAULT 0,
          cantidad integer NOT NULL DEFAULT 0,
          descripcion text,
          estado_vencido boolean NOT NULL DEFAULT FALSE,
          created_at timestamp DEFAULT CURRENT_TIMESTAMP,
          updated_at timestamp DEFAULT CURRENT_TIMESTAMP
        )");
      }
      $db->exec("ALTER TABLE lotes ADD COLUMN IF NOT EXISTS cantidad integer NOT NULL DEFAULT 0");
      $db->exec("ALTER TABLE lotes ADD COLUMN IF NOT EXISTS estado_vencido boolean NOT NULL DEFAULT FALSE");
      $db->exec("CREATE INDEX IF NOT EXISTS idx_lotes_producto ON lotes (id_producto)");
      $db->exec("CREATE INDEX IF NOT EXISTS idx_lotes_caducidad ON lotes (fecha_caducidad)");
      $done = true;
    } catch (Throwable $e) {}
  }
}

if (!function_exists('producto_lotes_es_vencido')) {
  function producto_lotes_es_vencido(array $row): bool {
    if (isset($row['estado_vencido']) && in_array($row['estado_vencido'], [true, 't', 'true', '1', 1], true)) {
      return true;
    }
    $f = $row['fecha_caducidad'] ?? null;
    if ($f !== null && $f !== '') {
      $d = strtotime((string)$f);
      if ($d !== false) return $d < strtotime(date('Y-m-d'));
    }
    return false;
  }
}

if (!function_exists('producto_lotes_marcar_vencidos')) {
  // Marca como vencidos los lotes cuya fecha ya pasó.
  function producto_lotes_marcar_vencidos(PDO $db): int {
    try {
      $stmt = $db->query("UPDATE lotes SET estado_vencido = TRUE WHERE (estado_vencido IS NULL OR estado_vencido = FALSE) AND fecha_caducidad < CURRENT_DATE");
      $n = $stmt->rowCount();
      $stmt->closeCursor();
      return $n;
    } catch (Throwable $e) { return 0; }
  }
}

if (!function_exists('producto_lotes_revertir_vencidos')) {
  // Si la fecha de un lote se renovó (hoy o futura), deja de estar vencido.
  function producto_lotes_revertir_vencidos(PDO $db): int {
    try {
      $stmt = $db->query("UPDATE lotes SET estado_vencido = FALSE WHERE estado_vencido = TRUE AND fecha_caducidad >= CURRENT_DATE");
      $n = $stmt->rowCount();
      $stmt->closeCursor();
      return $n;
    } catch (Throwable $e) { return 0; }
  }
}

if (!function_exists('producto_lotes_registrar')) {
  // Registra un lote nuevo (compra). Devuelve el lote creado.
  function producto_lotes_registrar(PDO $db, $idProducto, $cantidad, $fechaCaducidad = null, $precioCompra = null, $numeroLote = null, $descripcion = null): array {
    producto_lotes_asegurar_esquema($db);
    $id = intval($idProducto);
    $cant = max(0, (int)$cantidad);
    $precio = ($precioCompra !== null && $precioCompra !== '') ? (float)$precioCompra : 0;
    $fecha = ($fechaCaducidad !== null && $fechaCaducidad !== '') ? trim((string)$fechaCaducidad) : date('Y-m-d');
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$d || $d->format('Y-m-d') !== $fecha) { $fecha = date('Y-m-d'); }
    if ($numeroLote === null || trim((string)$numeroLote) === '') {
      $numeroLote = 'L' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
    try {
      $stmt = $db->prepare("INSERT INTO lotes (id_producto, numero_lote, fecha_caducidad, precio_compra_lote, cantidad, descripcion, estado_vencido) VALUES (?, ?, ?, ?, ?, ?, FALSE)");
      $stmt->execute([$id, trim((string)$numeroLote), $fecha, $precio, $cant, $descripcion]);
      $loteId = $db->lastInsertId();
      $stmt->closeCursor();
      return [
        'id_lote' => (int)$loteId,
        'id_producto' => $id,
        'numero_lote' => trim((string)$numeroLote),
        'fecha_caducidad' => $fecha,
        'precio_compra_lote' => $precio,
        'cantidad' => $cant,
        'descripcion' => $descripcion,
        'vencido' => false
      ];
    } catch (Throwable $e) {
      return ['error' => $e->getMessage()];
    }
  }
}

if (!function_exists('producto_lotes_listar_muchos')) {
  // Devuelve map id_producto => [lotes...] para muchos productos (una sola consulta).
  function producto_lotes_listar_muchos(PDO $db, array $ids): array {
    $out = [];
    if (empty($ids)) return $out;
    producto_lotes_asegurar_esquema($db);
    $idsNum = array_values(array_unique(array_map('intval', $ids)));
    if (empty($idsNum)) return $out;
    $place = rtrim(str_repeat('?,', count($idsNum)), ',');
    try {
      $stmt = $db->prepare("SELECT id_lote, id_producto, numero_lote, fecha_caducidad, precio_compra_lote, cantidad, descripcion, created_at, estado_vencido FROM lotes WHERE id_producto IN ($place) ORDER BY fecha_caducidad ASC, id_lote ASC");
      $stmt->execute($idsNum);
      while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $r['vencido'] = producto_lotes_es_vencido($r);
        $out[(int)$r['id_producto']][] = $r;
      }
      $stmt->closeCursor();
    } catch (Throwable $e) {}
    return $out;
  }
}

if (!function_exists('producto_lotes_listar')) {
  function producto_lotes_listar(PDO $db, $idProducto): array {
    $map = producto_lotes_listar_muchos($db, [$idProducto]);
    return $map[(int)$idProducto] ?? [];
  }
}
