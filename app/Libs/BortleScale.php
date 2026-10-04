<?php

namespace App\Libs;

/**
 * Class BortleScale
 *
 * Provides utilities and standard color/description mappings for the
 * 9-class Bortle Dark-Sky Scale used across Astronotify.
 */
class BortleScale
{
    /**
     * Get the standardized human-readable description for a given Bortle class.
     *
     * @param  int|null  $bortleClass  Bortle numeric class (1 to 9).
     * @return string Descriptive label for the sky darkness.
     */
    public static function description(?int $bortleClass): string
    {
        return match ($bortleClass) {
            1 => 'Class 1: Excellent truly dark sky',
            2 => 'Class 2: Truly dark site with negligible glow',
            3 => 'Class 3: Rural sky with detailed Milky Way',
            4 => 'Class 4: Rural/suburban transition',
            5 => 'Class 5: Suburban sky with moderate light pollution',
            6 => 'Class 6: Bright suburban sky',
            7 => 'Class 7: Suburban/urban transition',
            8 => 'Class 8: City sky with faint stars only',
            9 => 'Class 9: Inner-city sky',
            default => $bortleClass ? "Class {$bortleClass}" : 'Unknown sky rating',
        };
    }

    /**
     * Get the Tailwind CSS badge class strings corresponding to a Bortle class.
     *
     * @param  int|null  $bortleClass  Bortle numeric class (1 to 9).
     * @return string Tailwind CSS classes for border, background, and text colors.
     */
    public static function badgeColor(?int $bortleClass): string
    {
        return match ($bortleClass) {
            1 => 'text-emerald-400 bg-emerald-950/70 border-emerald-500/40',
            2 => 'text-teal-400 bg-teal-950/70 border-teal-500/40',
            3 => 'text-cyan-400 bg-cyan-950/70 border-cyan-500/40',
            4 => 'text-blue-400 bg-blue-950/70 border-blue-500/40',
            5 => 'text-yellow-400 bg-yellow-950/70 border-yellow-500/40',
            6 => 'text-amber-400 bg-amber-950/70 border-amber-500/40',
            default => 'text-orange-400 bg-orange-950/70 border-orange-500/40',
        };
    }
}
