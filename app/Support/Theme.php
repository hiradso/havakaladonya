<?php

namespace App\Support;

use App\Models\Setting;

/**
 * One place that decides the brand colors for BOTH the public site
 * (CSS variables in app.blade.php) and the Filament admin panel.
 *
 * Defaults follow the company logo: golden yellow + royal blue.
 */
class Theme
{
    /** Main color: buttons, highlights. A warm version of the logo yellow (#FFE500) that stays readable. */
    public const DEFAULT_PRIMARY = '#FFC20E';

    /** Second color: secondary accents (air/sea routes, links, info badges). The logo blue. */
    public const DEFAULT_SECONDARY = '#0026B4';

    public static function primary(): string
    {
        return self::color('primary_color', self::DEFAULT_PRIMARY);
    }

    public static function secondary(): string
    {
        return self::color('secondary_color', self::DEFAULT_SECONDARY);
    }

    /** Readable text color on top of the primary color. */
    public static function primaryForeground(): string
    {
        return self::foregroundFor(self::primary());
    }

    /** Readable text color on top of the secondary color. */
    public static function secondaryForeground(): string
    {
        return self::foregroundFor(self::secondary());
    }

    public static function foregroundFor(string $hex): string
    {
        return self::luminance($hex) > 0.45 ? '#0A0E1A' : '#FFFFFF';
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

    private static function color(string $key, string $default): string
    {
        $color = Setting::get($key, $default);

        return self::isHex($color) ? strtoupper($color) : $default;
    }
}
