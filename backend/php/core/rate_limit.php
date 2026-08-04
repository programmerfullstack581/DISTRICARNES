<?php
// =============================================
// RATE LIMIT - Limitador de intentos (fuerza bruta)
// =============================================
// Contadores basados en archivos bajo sys_get_temp_dir()/dc_rate_limit/
// (no requiere Redis/APCu y funciona en cualquier hosting como Render).
// Uso:
//   require_once __DIR__ . '/../core/rate_limit.php';
//   $rl = dc_rate_limit_consume('login:ip:' . dc_client_ip(), 10, 900);
//   if (!$rl['allowed']) { ... 429 ... }

if (!function_exists('dc_rate_limit_dir')) {
    function dc_rate_limit_dir(): string {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dc_rate_limit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir;
    }

    function dc_rate_limit_file(string $key): string {
        return dc_rate_limit_dir() . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    }

    // Limpia archivos vencidos (una vez por petición, evita acumular basura)
    function dc_rate_limit_cleanup(): void {
        static $cleaned = false;
        if ($cleaned) return;
        $cleaned = true;
        $dir = dc_rate_limit_dir();
        if (!is_dir($dir)) return;
        $files = @glob($dir . '/*.json');
        if ($files === false) return;
        $now = time();
        foreach ($files as $f) {
            if (@filemtime($f) < ($now - 3600)) {
                @unlink($f);
            }
        }
    }

    // Consume un intento y devuelve el estado resultante
    function dc_rate_limit_consume(string $key, int $maxAttempts, int $windowSeconds): array {
        dc_rate_limit_cleanup();
        $file = dc_rate_limit_file($key);
        $now  = time();
        $data = ['count' => 0, 'expires_at' => $now + $windowSeconds];

        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $tmp = @json_decode($raw, true);
            if (is_array($tmp)) {
                $data = $tmp;
            }
        }
        if ((int)($data['expires_at'] ?? 0) <= $now) {
            $data = ['count' => 0, 'expires_at' => $now + $windowSeconds];
        }
        $data['count'] = (int)$data['count'] + 1;
        @file_put_contents($file, json_encode($data), LOCK_EX);

        $allowed     = $data['count'] <= $maxAttempts;
        $remaining   = max(0, $maxAttempts - (int)$data['count']);
        $retryAfter  = $allowed ? 0 : max(1, (int)$data['expires_at'] - $now);
        return [
            'allowed'     => $allowed,
            'attempts'    => (int)$data['count'],
            'remaining'   => $remaining,
            'retry_after' => $retryAfter,
        ];
    }

    // Estado actual sin consumir intentos (para bloquear rápido)
    function dc_rate_limit_peek(string $key, int $maxAttempts, int $windowSeconds): array {
        dc_rate_limit_cleanup();
        $file = dc_rate_limit_file($key);
        $now  = time();
        $data = ['count' => 0, 'expires_at' => $now + $windowSeconds];

        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $tmp = @json_decode($raw, true);
            if (is_array($tmp)) {
                $data = $tmp;
            }
        }
        if ((int)($data['expires_at'] ?? 0) <= $now) {
            $data = ['count' => 0, 'expires_at' => $now + $windowSeconds];
        }
        return [
            'allowed'     => (int)$data['count'] < $maxAttempts,
            'count'       => (int)$data['count'],
            'retry_after' => max(0, (int)$data['expires_at'] - $now),
        ];
    }

    // Reinicia los contadores (p. ej. tras un login exitoso)
    function dc_rate_limit_reset(string $key): void {
        @unlink(dc_rate_limit_file($key));
    }

    // Dirección IP del cliente (usa X-Forwarded-For en hosting proxy como Render)
    function dc_client_ip(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts    = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim((string)$parts[0]); // primer salto = cliente original
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }
        return $ip;
    }
}
