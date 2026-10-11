<?php

namespace Tests\Unit;

use App\Libs\SunCalc;
use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Services\ISSTransitCalculator;
use DateTime;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ISSTransitCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_suncalc_get_refraction_degrees_calculates_physical_refraction(): void
    {
        $this->assertEquals(0.0, SunCalc::getRefractionDegrees(0.0));
        $this->assertEquals(0.0, SunCalc::getRefractionDegrees(-5.0));

        $refrHorizon = SunCalc::getRefractionDegrees(0.1);
        $this->assertGreaterThan(0.4, $refrHorizon);
        $this->assertLessThan(0.6, $refrHorizon);

        $refrFiveDeg = SunCalc::getRefractionDegrees(5.0);
        $this->assertGreaterThan(0.14, $refrFiveDeg);
        $this->assertLessThan(0.18, $refrFiveDeg);

        $refrHigh = SunCalc::getRefractionDegrees(45.0);
        $this->assertLessThan(0.03, $refrHigh);
    }

    public function test_sub_horizon_celestial_body_retains_negative_geometric_altitude(): void
    {
        // 2026-10-14 11:45:57 UTC at Lat: 54.04882, Lon: -2.801305
        $date = new DateTime('2026-10-14 11:45:57', new DateTimeZone('UTC'));
        $pos = SunCalc::getMoonPosition($date, 54.04882, -2.801305);

        $this->assertArrayHasKey('altitude_geometric', $pos);
        $this->assertLessThan(0.0, $pos['altitude_geometric']);
        // When geometrically below horizon, apparent altitude must not be lifted to a false positive
        $this->assertLessThanOrEqual(0.0, $pos['altitude']);
    }

    public function test_calculate_separation_accurately_computes_spherical_distance(): void
    {
        // Same position = 0
        $this->assertEquals(0.0, ISSTransitCalculator::calculateSeparation(45.0, 180.0, 45.0, 180.0));

        // 1 degree altitude separation at same azimuth
        $sep = ISSTransitCalculator::calculateSeparation(10.0, 90.0, 11.0, 90.0);
        $this->assertEqualsWithDelta(1.0, $sep, 0.001);
    }

    public function test_calculator_excludes_transits_below_minimum_altitude(): void
    {
        Setting::set('transit_min_altitude', 5.0);
        Setting::set('conjunction_threshold', 0.75);

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Home',
            'latitude' => 54.04882,
            'longitude' => -2.801305,
            'elevation' => 121,
            'is_active' => true,
            'notify_iss_sun_transit' => true,
            'notify_iss_moon_transit' => true,
            'notify_stargazing_alerts' => true,
        ]);

        $tle = "ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901";
        Http::fake([
            'celestrak.org/*' => Http::response($tle, 200),
        ]);
        Cache::forget('iss_tle_data');

        $calculator = new ISSTransitCalculator;
        $results = $calculator->calculateForLocation($location);

        // Verify that no transit is created with altitude < 5.0 degrees
        foreach ($results as $transit) {
            $this->assertGreaterThanOrEqual(5.0, $transit['altitude_degrees']);
        }

        $dbTransits = ISSTransit::where('location_id', $location->id)->get();
        foreach ($dbTransits as $transit) {
            $this->assertGreaterThanOrEqual(5.0, (float) $transit->altitude_degrees);
        }
    }
}
