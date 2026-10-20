<?php

namespace App\Support;

use DateTimeInterface;
use IntlDateFormatter;

/** Persian (Jalali) dates with Persian digits, in Tehran time — e.g. ۱۴۰۵/۰۷/۲۸ ۱۳:۳۰. */
class Jalali
{
    public static function format(DateTimeInterface|string|null $date, bool $withTime = false): ?string
    {
        if (blank($date)) {
            return null;
        }

        $date = $date instanceof DateTimeInterface ? $date : new \DateTimeImmutable($date);

        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            $withTime ? 'yyyy/MM/dd HH:mm' : 'yyyy/MM/dd',
        );

        return $formatter->format($date) ?: null;
    }
}
