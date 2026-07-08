<?php

namespace App\Libs;

use DateTime;

/**
 * SunCalc astronomical calculations library.
 * Computes positions, coordinates, and phases for the Sun and Moon.
 * Adapted from the original Javascript SunCalc library by Vladimir Agafonkin.
 */
class SunCalc
{
    // Mathematical and Astronomical Constants
    const PI = 3.14159265358979323846;
    const RAD = 0.017453292519943295; // Degrees to Radians conversion factor (PI / 180)
    const DAY_MS = 86400000;          // Milliseconds in a solar day
    const J1970 = 2440588;            // Julian Date for Unix Epoch (1970-01-01)
    const J2000 = 2451545;            // Julian Date for J2000 epoch reference
    const EARTH_RADIUS = 6378.14;     // Earth equatorial radius in km

    /**
     * Converts a DateTime instance to Julian days offset from J2000.
     */
    private static function toDays(DateTime $date): float
    {
        $timestamp = $date->getTimestamp() + (float) $date->format('u') / 1000000;
        return ($timestamp * 1000) / self::DAY_MS - 0.5 + self::J1970 - self::J2000;
    }

    /**
     * Calculates the Terrestrial Time offset (Delta T) for Julian day offset.
     */
    private static function deltaT(float $d): float
    {
        $y = 2000 + $d / 365.2425;
        if ($y < 1920) {
            $t = $y - 1900;
            return -2.79 + $t * (1.494119 + $t * (-0.0598939 + $t * (0.0061966 - $t * 0.000197)));
        }
        if ($y < 1941) {
            $t = $y - 1920;
            return 21.20 + $t * (0.84493 + $t * (-0.076100 + $t * 0.0020936));
        }
        if ($y < 1961) {
            $t = $y - 1950;
            return 29.07 + $t * (0.407 + $t * (-1 / 233 + $t / 2547));
        }
        if ($y < 1986) {
            $t = $y - 1975;
            return 45.45 + $t * (1.067 + $t * (-1 / 260 - $t / 718));
        }
        if ($y < 2005) {
            $t = $y - 2000;
            return 63.86 + $t * (0.3345 + $t * (-0.060374 + $t * (0.0017275 + $t * (0.000651814 + $t * 0.00002373599))));
        }
        if ($y < 2050) {
            $t = $y - 2000;
            return 62.92 + $t * (0.32217 + $t * 0.005589);
        }
        $t = ($y - 1820) / 100;
        return -20 + 32 * $t * $t - 0.5628 * (2150 - $y);
    }

    /**
     * Converts J2000 days to Terrestrial Time J2000 days.
     */
    private static function toDaysTT(float $d): float
    {
        return $d + self::deltaT($d) / 86400;
    }

    /**
     * Calculates the horizontal coordinate Azimuth.
     */
    private static function azimuth(float $H, float $phi, float $dec): float
    {
        $val = atan2(sin($H), cos($H) * sin($phi) - tan($dec) * cos($phi)) / self::RAD + 540;
        return fmod($val, 360);
    }

    /**
     * Calculates the horizontal coordinate Altitude.
     */
    private static function altitude(float $H, float $phi, float $dec): float
    {
        return asin(sin($phi) * sin($dec) + cos($phi) * cos($dec) * cos($H));
    }

    /**
     * Calculates mean sidereal time.
     */
    private static function siderealTime(float $d, float $lw): float
    {
        return self::RAD * (280.46061837 + 360.98564736629 * $d) - $lw;
    }

    /**
     * Calculates atmospheric refraction correction for elevation.
     */
    private static function astroRefraction(float $h): float
    {
        if ($h < 0) {
            $h = 0;
        }
        return 0.0002967 / tan($h + 0.00312536 / ($h + 0.08901179));
    }

    /**
     * Calculates spherical coordinates (RA/Dec) for the Sun.
     */
    private static function sunCoords(float $d): array
    {
        $t = $d / 36525; // centuries since J2000
        $L0 = self::RAD * (280.46646 + $t * (36000.76983 + $t * 0.0003032));
        $M = self::RAD * (357.52911 + $t * (35999.05029 - $t * 0.0001537));
        $sinM = sin($M);
        $cosM = cos($M);
        $C = self::RAD * ((1.914602 - $t * (0.004817 + $t * 0.000014)) * $sinM +
            (0.019993 - 0.000101 * $t) * 2 * $sinM * $cosM + 0.000289 * $sinM * (3 - 4 * $sinM * $sinM));
        $Om = self::RAD * (125.04 - 1934.136 * $t);
        $L = $L0 + $C - self::RAD * (0.00569 + 0.00478 * sin($Om));
        $e = self::RAD * (23.439291 - $t * (0.0130042 + $t * (0.00000016 - $t * 0.000000504))) + self::RAD * 0.00256 * cos($Om);

        return [
            'ra' => atan2(sin($L) * cos($e), cos($L)),
            'dec' => asin(sin($e) * sin($L))
        ];
    }

    /**
     * Returns the position of the Sun for a given date, latitude, and longitude.
     */
    public static function getPosition(DateTime $date, float $lat, float $lng): array
    {
        $lw = self::RAD * -$lng;
        $phi = self::RAD * $lat;
        $d = self::toDays($date);

        $c = self::sunCoords(self::toDaysTT($d));
        $H = self::siderealTime($d, $lw) - $c['ra'];
        $h = self::altitude($H, $phi, $c['dec']);

        return [
            'azimuth' => self::azimuth($H, $phi, $c['dec']),
            'altitude' => ($h + self::astroRefraction($h)) / self::RAD
        ];
    }

    /**
     * Calculates nutation in longitude and obliquity.
     */
    private static function nutationObliquity(float $t): array
    {
        $om = self::RAD * (125.04452 - 1934.136261 * $t);
        $ls = self::RAD * (280.4665 + 36000.7698 * $t);
        $lm = self::RAD * (218.3165 + 481267.8813 * $t);
        $dpsi = (-17.20 * sin($om) - 1.32 * sin(2 * $ls) - 0.23 * sin(2 * $lm) + 0.21 * sin(2 * $om)) / 3600;
        $deps = (9.20 * cos($om) + 0.57 * cos(2 * $ls) + 0.10 * cos(2 * $lm) - 0.09 * cos(2 * $om)) / 3600;
        $eps0 = 23.439291 - $t * (0.0130042 + $t * (0.00000016 - $t * 0.000000504));
        return [
            'dpsi' => $dpsi,
            'eps' => self::RAD * ($eps0 + $deps)
        ];
    }

    /**
     * Calculates spherical coordinates (RA/Dec) and distance for the Moon.
     */
    private static function moonCoords(float $d): array
    {
        $t = $d / 36525; // centuries since J2000

        $Lp = 218.3164477 + $t * (481267.88123421 + $t * (-0.0015786 + $t * (1 / 538841 - $t / 65194000)));
        $D = 297.8501921 + $t * (445267.1114034 + $t * (-0.0018819 + $t * (1 / 545868 - $t / 113065000)));
        $M = 357.5291092 + $t * (35999.0502909 + $t * (-0.0001536 + $t / 24490000));
        $Mp = 134.9633964 + $t * (477198.8675055 + $t * (0.0087414 + $t * (1 / 69699 - $t / 14712000)));
        $F = 93.2720950 + $t * (483202.0175233 + $t * (-0.0036539 + $t * (-1 / 3526000 + $t / 863310000)));
        $A1 = 119.75 + 131.849 * $t;
        $A2 = 53.09 + 479264.290 * $t;
        $A3 = 313.45 + 481266.484 * $t;
        $E = 1 - $t * (0.002516 + $t * 0.0000074);

        $Dr = self::RAD * $D;
        $Mr = self::RAD * $M;
        $Mpr = self::RAD * $Mp;
        $Fr = self::RAD * $F;
        
        $sl = 0;
        $sr = 0;
        $sb = 0;

        $lonLen = count(MoonData::$moonLon);
        for ($i = 0; $i < $lonLen; $i += 6) {
            $m = MoonData::$moonLon[$i + 1];
            $arg = MoonData::$moonLon[$i] * $Dr + $m * $Mr + MoonData::$moonLon[$i + 2] * $Mpr + MoonData::$moonLon[$i + 3] * $Fr;
            $f = ($m === 1 || $m === -1) ? $E : (($m === 2 || $m === -2) ? $E * $E : 1);
            $sl += MoonData::$moonLon[$i + 4] * $f * sin($arg);
            $sr += MoonData::$moonLon[$i + 5] * $f * cos($arg);
        }

        $latLen = count(MoonData::$moonLat);
        for ($i = 0; $i < $latLen; $i += 5) {
            $m = MoonData::$moonLat[$i + 1];
            $arg = MoonData::$moonLat[$i] * $Dr + $m * $Mr + MoonData::$moonLat[$i + 2] * $Mpr + MoonData::$moonLat[$i + 3] * $Fr;
            $f = ($m === 1 || $m === -1) ? $E : (($m === 2 || $m === -2) ? $E * $E : 1);
            $sb += MoonData::$moonLat[$i + 4] * $f * sin($arg);
        }

        $A1r = self::RAD * $A1;
        $Lpr = self::RAD * $Lp;
        $sl += 3958 * sin($A1r) + 1962 * sin($Lpr - $Fr) + 318 * sin(self::RAD * $A2);
        $sb += -2235 * sin($Lpr) + 382 * sin(self::RAD * $A3) + 175 * sin($A1r - $Fr) + 175 * sin($A1r + $Fr) +
            127 * sin($Lpr - $Mpr) - 115 * sin($Lpr + $Mpr);

        $no = self::nutationObliquity($t);
        $l = self::RAD * ($Lp + $sl / 1e6 + $no['dpsi']);
        $b = self::RAD * ($sb / 1e6);

        return [
            'ra' => atan2(sin($l) * cos($no['eps']) - tan($b) * sin($no['eps']), cos($l)),
            'dec' => asin(sin($b) * cos($no['eps']) + cos($b) * sin($no['eps']) * sin($l)),
            'dist' => 385000.56 + $sr / 1000
        ];
    }

    /**
     * Calculates the Moon's phase fraction, phase angle, and illuminated disk fraction.
     */
    public static function getMoonIllumination(DateTime $date): array
    {
        $d = self::toDays($date);
        $s = self::sunCoords($d);
        $m = self::moonCoords($d);

        $sdist = 149598000; // distance from Earth to Sun in km

        $cosPhi = sin($s['dec']) * sin($m['dec']) + cos($s['dec']) * cos($m['dec']) * cos($s['ra'] - $m['ra']);
        $cosPhi = max(-1.0, min(1.0, $cosPhi));
        $phi = acos($cosPhi);

        $inc = atan2($sdist * sin($phi), $m['dist'] - $sdist * cos($phi));

        $fraction = (1 + cos($inc)) / 2;

        $angle = atan2(
            cos($s['dec']) * sin($s['ra'] - $m['ra']),
            sin($s['dec']) * cos($m['dec']) - cos($s['dec']) * sin($m['dec']) * cos($s['ra'] - $m['ra'])
        );

        $phase = 0.5 + 0.5 * $inc * ($angle < 0 ? -1 : 1) / self::PI;

        return [
            'fraction' => $fraction,
            'phase' => $phase,
            'angle' => $angle
        ];
    }

    /**
     * Returns the position of the Moon for a given date, latitude, and longitude.
     */
    public static function getMoonPosition(DateTime $date, float $lat, float $lng): array
    {
        $lw = self::RAD * -$lng;
        $phi = self::RAD * $lat;
        $d = self::toDays($date);
        $c = self::moonCoords(self::toDaysTT($d));
        $H = self::siderealTime($d, $lw) - $c['ra'];

        $hGeo = self::altitude($H, $phi, $c['dec']);
        $h = $hGeo - asin(self::EARTH_RADIUS / $c['dist'] * cos($hGeo));

        return [
            'azimuth' => self::azimuth($H, $phi, $c['dec']),
            'altitude' => ($h + self::astroRefraction($h)) / self::RAD,
            'distance' => $c['dist']
        ];
    }
}
