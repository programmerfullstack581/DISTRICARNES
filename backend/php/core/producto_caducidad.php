<?php
// =============================================
// Validación central de caducidad de productos
// Requiere que $conexion (PDO) ya esté definido.
// =============================================

if (!function_exists('producto_caducidad_asegurar_esquema')) {
  // Crea la columna estado_vencido si no existe (idempotente).
  function producto_caducidad_asegurar_esquema(PDO $db): void {
    try {
      $db->exec("ALTER TABLE producto ADD COLUMN IF NOT EXISTS estado_vencido BOOLEAN NOT NULL DEFAULT FALSE");
    } catch (Throwable $e) {}
  }
}

if (!function_exists('producto_caducidad_tiene_columna')) {
  function producto_caducidad_tiene_columna(PDO $db, string $col): bool {
    static $cache = [];
    if (array_key_exists($col, $cache)) return $cache[$col];
    try {
      $stmt = $db->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = 'producto' AND column_name = ? LIMIT 1");
      $stmt->execute([$col]);
      $cache[$col] = (bool)$stmt->fetch();
      $stmt->closeCursor();
    } catch (Throwable $e) {
      $cache[$col] = false;
    }
    return $cache[$col];
  }
}

if (!function_exists('producto_caducidad_marcar_vencidos')) {
  // Pasa a "vencido" todo producto cuya fecha de caducidad ya pasó.
  function producto_caducidad_marcar_vencidos(PDO $db): int {
    if (!producto_caducidad_tiene_columna($db, 'fecha_caducidad') || !producto_caducidad_tiene_columna($db, 'estado_vencido')) {
      return 0;
    }
    try {
      $stmt = $db->query("UPDATE producto SET estado_vencido = TRUE WHERE (estado_vencido IS NULL OR estado_vencido = FALSE) AND fecha_caducidad IS NOT NULL AND fecha_caducidad < CURRENT_DATE");
      $n = $stmt->rowCount();
      $stmt->closeCursor();
      return $n;
    } catch (Throwable $e) {
      return 0;
    }
  }
}

if (!function_exists('producto_caducidad_revertir')) {
  // Si se renovó la fecha (hoy o futura), el producto deja de estar vencido.
  function producto_caducidad_revertir(PDO $db): int {
    if (!producto_caducidad_tiene_columna($db, 'fecha_caducidad') || !producto_caducidad_tiene_columna($db, 'estado_vencido')) {
      return 0;
    }
    try {
      $stmt = $db->query("UPDATE producto SET estado_vencido = FALSE WHERE estado_vencido = TRUE AND fecha_caducidad IS NOT NULL AND fecha_caducidad >= CURRENT_DATE");
      $n = $stmt->rowCount();
      $stmt->closeCursor();
      return $n;
    } catch (Throwable $e) {
      return 0;
    }
  }
}

if (!function_exists('producto_caducidad_aplicar')) {
  // Rutina completa. Llamar en endpoints que listan o venden productos.
  function producto_caducidad_aplicar(PDO $db): array {
    // Mantener también el esquema y estado de los lotes por producto
    require_once __DIR__ . '/producto_lotes.php';
    producto_lotes_asegurar_esquema($db);
    producto_lotes_marcar_vencidos($db);
    producto_lotes_revertir_vencidos($db);
    // Estado de vencido a nivel de producto
    producto_caducidad_asegurar_esquema($db);
    $marcados = producto_caducidad_marcar_vencidos($db);
    $revertidos = producto_caducidad_revertir($db);
    return ['marcados' => $marcados, 'revertidos' => $revertidos];
  }
}

if (!function_exists('producto_caducidad_es_vencido')) {
  // Verifica por ID si un producto está vencido (para checkout/POS).
  function producto_caducidad_es_vencido(PDO $db, $productId): bool {
    producto_caducidad_asegurar_esquema($db);
    $id = intval($productId);
    if ($id <= 0) return false;
    try {
      $stmt = $db->prepare("SELECT fecha_caducidad, estado_vencido FROM producto WHERE id_producto = ? LIMIT 1");
      $stmt->execute([$id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt->closeCursor();
      if (!$row) return false;
      if (isset($row['estado_vencido']) && in_array($row['estado_vencido'], [true, 't', 'true', '1', 1], true)) {
        return true;
      }
      $f = $row['fecha_caducidad'] ?? null;
      if ($f !== null && $f !== '') {
        $d = strtotime((string)$f);
        if ($d !== false) return $d < strtotime(date('Y-m-d'));
      }
      return false;
    } catch (Throwable $e) {
      return false;
    }
  }
}

if (!function_exists('producto_caducidad_filtro_excluir')) {
  // Condición SQL para EXCLUIR vencidos. Devuelve '' si la columna no existe.
  function producto_caducidad_filtro_excluir(PDO $db): string {
    if (producto_caducidad_tiene_columna($db, 'estado_vencido')) {
      return "(estado_vencido IS NULL OR estado_vencido = FALSE)";
    }
    return '';
  }
}

if (!function_exists('producto_caducidad_es_vencido_registro')) {
  // Evalúa un array de producto (fila de BD) y devuelve bool.
  function producto_caducidad_es_vencido_registro(array $row): bool {
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
