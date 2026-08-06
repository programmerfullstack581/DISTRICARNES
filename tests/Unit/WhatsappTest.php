<?php
declare(strict_types=1);

namespace DistriCarnes\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class WhatsappTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../../backend/php/core/whatsapp_sender.php';
    }

    public function testNormalizaCelularColombianoSinPrefijo(): void
    {
        $this->assertSame('573015210177', dc_whatsapp_to_e164('3015210177'));
    }

    public function testNormalizaConPrefijo57(): void
    {
        $this->assertSame('573015210177', dc_whatsapp_to_e164('573015210177'));
    }

    public function testNormalizaConCeroLiderado(): void
    {
        $this->assertSame('573015210177', dc_whatsapp_to_e164('03015210177'));
    }

    public function testRechazaNumeroVacio(): void
    {
        $this->assertSame('', dc_whatsapp_to_e164(''));
        $this->assertSame('', dc_whatsapp_to_e164('abc'));
    }

    public function testNotificacionSinConfiguracionFallaSilenciosamente(): void
    {
        $res = dc_notify_new_order(1, 'test@example.com', 15000.0, 'nequi');
        $this->assertIsArray($res);
        $this->assertArrayHasKey('ok', $res);
        // Sin credenciales Twilio no debe lanzar excepción, solo devolver false.
        $this->assertSame(false, $res['ok']);
    }
}
