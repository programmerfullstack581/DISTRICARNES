<?php
// =============================================
// Caché de respuestas JSON (por archivo, TTL corto)
// - cache_respuesta_probar(): si hay copia fresca la emite y devuelve true (el script debe exit)
// - cache_respuesta_guardar(): guarda la respuesta para el TTL
// La clave incluye REQUEST_URI, así que cada combinación de parámetros tiene su copia.
// =============================================

if (!function_exists('cache_respuesta_clave')) {
  function cache_respuesta_clave(string $namespace): string {
    return $namespace . '|' . ($_SERVER['REQUEST_URI'] ?? '');
  }
}

if (!function_exists('cache_respuesta_dir')) {
  function cache_respuesta_dir(): string {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'districarnes_cache';
    if (!is_dir($dir)) {
      @mkdir($dir, 0777, true);
    }
    return $dir;
  }
}

if (!function_exists('cache_respuesta_probar')) {
  function cache_respuesta_probar(int $ttl, string $namespace = ''): bool {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return false;
    $file = cache_respuesta_dir() . DIRECTORY_SEPARATOR . 'resp_' . md5(cache_respuesta_clave($namespace)) . '.json';
    if (is_file($file)) {
      $mtime = @filemtime($file);
      if ($mtime !== false && (time() - $mtime) < $ttl) {
        $body = @file_get_contents($file);
        if ($body !== false && $body !== '') {
          if (!headers_sent()) { header('X-Cache: HIT'); }
          echo $body;
          return true;
        }
      }
    }
    if (!headers_sent()) { header('X-Cache: MISS'); }
    return false;
  }
}

if (!function_exists('cache_respuesta_guardar')) {
  function cache_respuesta_guardar($data, string $namespace = ''): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;
    $file = cache_respuesta_dir() . DIRECTORY_SEPARATOR . 'resp_' . md5(cache_respuesta_clave($namespace)) . '.json';
    $body = json_encode($data);
    if ($body !== false) {
      @file_put_contents($file, $body, LOCK_EX);
    }
  }
}

if (!function_exists('cache_respuesta_invalidar')) {
  // Borra todas las copias cacheadas (usar tras escrituras que cambian ventas/inventario).
  function cache_respuesta_invalidar(): void {
    $dir = cache_respuesta_dir();
    $files = @glob($dir . DIRECTORY_SEPARATOR . 'resp_*.json');
    if ($files) {
      foreach ($files as $f) {
        @unlink($f);
      }
    }
  }
}
