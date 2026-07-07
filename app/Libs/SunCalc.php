<?php

namespace App\Libs;

use DateTime;

class SunCalc
{
    const PI = 3.14159265358979323846;
    const RAD = 0.017453292519943295; // PI / 180
    const DAY_MS = 86400000;
    const J1970 = 2440588;
    const J2000 = 2451545;
    const EARTH_RADIUS = 6378.14; // equatorial radius in km, for the Moon's topocentric parallax

    private static $moonLon = [
        0, 0, 1, 0, 6288774, -20905355,
        2, 0, -1, 0, 1274027, -3699111,
        2, 0, 0, 0, 658314, -2955968,
        0, 0, 2, 0, 213618, -569925,
        0, 1, 0, 0, -185116, 48888,
        0, 0, 0, 2, -114332, -3149,
        2, 0, -2, 0, 58793, 246158,
        2, -1, -1, 0, 57066, -152138,
        2, 0, 1, 0, 53322, -170733,
        2, -1, 0, 0, 45758, -204586,
        0, 1, -1, 0, -40923, -129620,
        1, 0, 0, 0, -34720, 108743,
        0, 1, 1, 0, -30383, 104755,
        2, 0, 0, -2, 15327, 10321,
        0, 0, 1, 2, -12528, 0,
        0, 0, 1, -2, 10980, 79661,
        4, 0, -1, 0, 10675, -34782,
        0, 0, 3, 0, 10034, -23210,
        4, 0, -2, 0, 8548, -21636,
        2, 1, -1, 0, -7888, 24208,
        2, 1, 0, 0, -6766, 30824,
        1, 0, -1, 0, -5163, -8379,
        1, 1, 0, 0, 4987, -16675,
        2, -1, 1, 0, 4036, -12831,
        2, 0, 2, 0, 3994, -10445,
        4, 0, 0, 0, 3861, -11650,
        2, 0, -3, 0, 3665, 14403,
        0, 1, -2, 0, -2689, -7003,
        2, 0, -1, 2, -2602, 0,
        2, -1, -2, 0, 2390, 10056,
        1, 0, 1, 0, -2348, 6322,
        2, -2, 0, 0, 2236, -9884,
        0, 1, 2, 0, -2120, 5751,
        0, 2, 0, 0, -2069, 0,
        2, -2, -1, 0, 2048, -4950,
        2, 0, 1, -2, -1773, 4130,
        2, 0, 0, 2, -1595, 0,
        4, -1, -1, 0, 1215, -3958,
        0, 0, 2, 2, -1110, 0,
        3, 0, -1, 0, -892, 3258,
        2, 1, 1, 0, -810, 2616,
        4, -1, -2, 0, 759, -1897,
        0, 2, -1, 0, -713, -2117,
        2, 2, -1, 0, -700, 2354,
        2, 1, -2, 0, 691, 0,
        2, -1, 0, -2, 596, 0,
        4, 0, 1, 0, 549, -1423,
        0, 0, 4, 0, 537, -1117,
        4, -1, 0, 0, 520, -1571,
        1, 0, -2, 0, -487, -1739,
        2, 1, 0, -2, -399, 0,
        0, 0, 2, -2, -381, -4421,
        1, 1, 1, 0, 351, 0,
        3, 0, -2, 0, -340, 0,
        4, 0, -3, 0, 330, 0,
        2, -1, 2, 0, 327, 0,
        0, 2, 1, 0, -323, 1165,
        1, 1, -1, 0, 299, 0,
        2, 0, 3, 0, 294, 0,
        2, 0, -1, -2, 0, 8752
    ];

    private static $moonLat = [
        0, 0, 0, 1, 5128122,
        0, 0, 1, 1, 280602,
        0, 0, 1, -1, 277693,
        2, 0, 0, -1, 173237,
        2, 0, -1, 1, 55413,
        2, 0, -1, -1, 46271,
        2, 0, 0, 1, 32573,
        0, 0, 2, 1, 17198,
        2, 0, 1, -1, 9266,
        0, 0, 2, -1, 8822,
        2, -1, 0, -1, 8216,
        2, 0, -2, -1, 4324,
        2, 0, 1, 1, 4200,
        2, 1, 0, -1, -3359,
        2, -1, -1, 1, 2463,
        2, -1, 0, 1, 2211,
        2, -1, -1, -1, 2065,
        0, 1, -1, -1, -1870,
        4, 0, -1, -1, 1828,
        0, 1, 0, 1, -1794,
        0, 0, 0, 3, -1749,
        0, 1, -1, 1, -1565,
        1, 0, 0, 1, -1491,
        0, 1, 1, 1, -1475,
        0, 1, 1, -1, -1410,
        0, 1, 0, -1, -1344,
        1, 0, 0, -1, -1335,
        0, 0, 3, 1, 1107,
        4, 0, 0, -1, 1021,
        4, 0, -1, 1, 833,
        0, 0, 1, -3, 777,
        4, 0, -2, 1, 671,
        2, 0, 0, -3, 607,
        2, 0, 2, -1, 596,
        2, -1, 1, -1, 491,
        2, 0, -2, 1, -451,
        0, 0, 3, -1, 439,
        2, 0, 2, 1, 422,
        2, 0, -3, -1, 421,
        2, 1, -1, 1, -366,
        2, 1, 0, 1, -351,
        4, 0, 0, 1, 331,
        2, -1, 1, 1, 315,
        2, -2, 0, -1, 302,
        0, 0, 1, 3, -283,
        2, 1, 1, -1, -229,
        1, 1, 0, -1, 223,
        1, 1, 0, 1, 223,
        0, 1, -2, -1, -220,
        2, 1, -1, -1, -220,
        1, 0, 1, 1, -185,
        2, -1, -2, -1, 181,
        0, 1, 2, 1, -177,
        4, 0, -2, -1, 176,
        4, -1, -1, -1, 166,
        1, 0, 1, -1, -164,
        4, 0, 1, -1, 132,
        1, 0, -1, -1, -119,
        4, -1, 0, -1, 115,
        2, -2, 0, 1, 107
    ];

    private static function toDays(DateTime $date)
    {
        $timestamp = $date->getTimestamp() + (float) $date->format('u') / 1000000;
        return ($timestamp * 1000) / self::DAY_MS - 0.5 + self::J1970 - self::J2000;
    }

    private static function deltaT($d)
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

    private static function toDaysTT($d)
    {
        return $d + self::deltaT($d) / 86400;
    }

    private static function azimuth($H, $phi, $dec)
    {
        $val = atan2(sin($H), cos($H) * sin($phi) - tan($dec) * cos($phi)) / self::RAD + 540;
        return fmod($val, 360);
    }

    private static function altitude($H, $phi, $dec)
    {
        return asin(sin($phi) * sin($dec) + cos($phi) * cos($dec) * cos($H));
    }

    private static function siderealTime($d, $lw)
    {
        return self::RAD * (280.46061837 + 360.98564736629 * $d) - $lw;
    }

    private static function astroRefraction($h)
    {
        if ($h < 0) {
            $h = 0;
        }
        return 0.0002967 / tan($h + 0.00312536 / ($h + 0.08901179));
    }

    private static function sunCoords($d)
    {
        $t = $d / 36525;
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

    public static function getPosition(DateTime $date, $lat, $lng)
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

    private static function nutationObliquity($t)
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

    private static function moonCoords($d)
    {
        $t = $d / 36525;

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

        $lonLen = count(self::$moonLon);
        for ($i = 0; $i < $lonLen; $i += 6) {
            $m = self::$moonLon[$i + 1];
            $arg = self::$moonLon[$i] * $Dr + $m * $Mr + self::$moonLon[$i + 2] * $Mpr + self::$moonLon[$i + 3] * $Fr;
            $f = ($m === 1 || $m === -1) ? $E : (($m === 2 || $m === -2) ? $E * $E : 1);
            $sl += self::$moonLon[$i + 4] * $f * sin($arg);
            $sr += self::$moonLon[$i + 5] * $f * cos($arg);
        }

        $latLen = count(self::$moonLat);
        for ($i = 0; $i < $latLen; $i += 5) {
            $m = self::$moonLat[$i + 1];
            $arg = self::$moonLat[$i] * $Dr + $m * $Mr + self::$moonLat[$i + 2] * $Mpr + self::$moonLat[$i + 3] * $Fr;
            $f = ($m === 1 || $m === -1) ? $E : (($m === 2 || $m === -2) ? $E * $E : 1);
            $sb += self::$moonLat[$i + 4] * $f * sin($arg);
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

    public static function getMoonPosition(DateTime $date, $lat, $lng)
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
