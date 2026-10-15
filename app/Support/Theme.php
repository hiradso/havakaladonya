<?php

namespace App\Support;

use App\Models\Setting;

/**
 * One place that decides the brand color for BOTH the public site
 * (CSS variables in app.blade.php) and the Filament admin panel.
 */
class Theme
{
    public const DEFAULT_PRIMARY = '#FFB020';

    public static function primary(): string
    {
        $color = Setting::get('primary_color', self::DEFAULT_PRIMARY);

        return self::isHex($color) ? strtoupper($color) : self::DEFAULT_PRIMARY;
    }

    /**
     * Text color that stays readable on top of the primary color
     * (dark text on light colors such as yellow, white text on dark colors).
     */
    public static function primaryForeground(): string
    {
        return self::luminance(self::primary()) > 0.45 ? '#0A0E1A' : '#FFFFFF';
    }

    public static function isHex(mixed $color): bool
    {
        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }

    /** Relative luminance (WCAG) of a #RRGGBB color, 0 = black, 1 = white. */
    public static function luminance(string $hex): float
    {
        $channels = array_map(
            fn (string $c): float => hexdec($c) / 255,
            str_split(ltrim($hex, '#'), 2),
        );

        [$r, $g, $b] = array_map(
            fn (float $c): float => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
