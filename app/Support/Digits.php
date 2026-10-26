<?php

namespace App\Support;

/** Persian digits for text shown on the Persian site (0-9 → ۰-۹). */
class Digits
{
    private const LATIN = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    private const PERSIAN = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    public static function toPersian(string $text): string
    {
        return str_replace(self::LATIN, self::PERSIAN, $text);
    }

    /** Persian (۰-۹) and Arabic (٠-٩) digits → 0-9, e.g. for phone numbers typed in the panel. */
    public static function toLatin(string $text): string
    {
        return str_replace([...self::PERSIAN, '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'], [...self::LATIN, ...self::LATIN], $text);
    }

    /** Only the visible text of HTML; tags, links and image addresses stay as they are. */
    public static function toPersianHtml(string $html): string
    {
        return preg_replace_callback('/>([^<]+)</u', fn (array $m) => '>'.self::toPersian($m[1]).'<', $html) ?? $html;
    }
}
