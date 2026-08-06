<?php
declare(strict_types=1);

namespace DistriCarnes\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../../backend/php/core/rate_limit.php';
    }

    public function testConsumePermiteHastaElMaximo(): void
    {
        $key = 'test:' . uniqid('', true);
        for ($i = 1; $i <= 3; $i++) {
            $res = dc_rate_limit_consume($key, 3, 60);
            $this->assertTrue($res['allowed'], "Intento $i debería permitirse");
            $this->assertSame($i, $res['attempts']);
        }
    }

    public function testConsumeBloqueaDespuesDelMaximo(): void
    {
        $key = 'test:' . uniqid('', true);
        for ($i = 0; $i < 3; $i++) {
            dc_rate_limit_consume($key, 3, 60);
        }
        $res = dc_rate_limit_consume($key, 3, 60);
        $this->assertFalse($res['allowed']);
        $this->assertGreaterThan(0, $res['retry_after']);
    }

    public function testResetLimpiaContadores(): void
    {
        $key = 'test:' . uniqid('', true);
        dc_rate_limit_consume($key, 1, 60);
        dc_rate_limit_reset($key);
        $res = dc_rate_limit_consume($key, 1, 60);
        $this->assertTrue($res['allowed']);
        $this->assertSame(1, $res['attempts']);
    }

    public function testPeekNoConsumeIntentos(): void
    {
        $key = 'test:' . uniqid('', true);
        dc_rate_limit_consume($key, 3, 60);
        $peek = dc_rate_limit_peek($key, 3, 60);
        $this->assertTrue($peek['allowed']);
        $this->assertSame(1, $peek['count']);
        // Después de peek, el contador sigue en 1 para el siguiente consume
        $res = dc_rate_limit_consume($key, 3, 60);
        $this->assertSame(2, $res['attempts']);
    }
}
