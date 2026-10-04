<?php

namespace Tests\Unit;

use App\Libs\SunCalc;
use DateTime;
use Tests\TestCase;

class SunCalcTest extends TestCase
{
    public function test_get_moon_illumination(): void
    {
        $date = new DateTime('2026-06-22 00:00:00');
        $illum = SunCalc::getMoonIllumination($date);

        $this->assertArrayHasKey('fraction', $illum);
        $this->assertArrayHasKey('phase', $illum);
        $this->assertArrayHasKey('angle', $illum);

        $this->assertGreaterThanOrEqual(0.0, $illum['fraction']);
        $this->assertLessThanOrEqual(1.0, $illum['fraction']);
        $this->assertGreaterThanOrEqual(0.0, $illum['phase']);
        $this->assertLessThanOrEqual(1.0, $illum['phase']);

        $this->assertEqualsWithDelta(0.51, $illum['fraction'], 0.01);
        $this->assertEqualsWithDelta(0.25, $illum['phase'], 0.01);
    }
}
