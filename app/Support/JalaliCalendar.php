<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Gregorian ⇄ Jalali (Persian) calendar arithmetic in plain PHP (no intl needed),
 * used to group the dashboard charts by Persian day / week / month.
 */
class JalaliCalendar
{
    public const MONTHS = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

    /** @return array{0: int, 1: int, 2: int} [year, month, day] */
    public static function fromGregorian(int $gy, int $gm, int $gd): array
    {
        $gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $gdm[$gm - 1];
        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        $jm = $days < 186 ? 1 + intdiv($days, 31) : 7 + intdiv($days - 186, 30);
        $jd = 1 + ($days < 186 ? $days % 31 : ($days - 186) % 30);

        return [$jy, $jm, $jd];
    }

    /** @return array{0: int, 1: int, 2: int} [year, month, day] */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + (intdiv($jy, 33) * 8) + intdiv(($jy % 33) + 3, 4) + $jd + ($jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;
        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }
        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        $gd = $days + 1;
        $leap = ($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0;
        $months = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for ($gm = 1; $gm <= 12 && $gd > $months[$gm]; $gm++) {
            $gd -= $months[$gm];
        }

        return [$gy, $gm, $gd];
    }

    /** @return array{0: int, 1: int, 2: int} */
    public static function of(CarbonInterface $date): array
    {
        return self::fromGregorian((int) $date->year, (int) $date->month, (int) $date->day);
    }

    /** Persian month name + year, e.g. "آبان ۱۴۰۵". */
    public static function monthLabel(int $jy, int $jm): string
    {
        return self::MONTHS[$jm - 1].' '.Digits::toPersian((string) $jy);
    }

    /** e.g. "۴ آبان" */
    public static function dayLabel(CarbonInterface $date): string
    {
        [, $jm, $jd] = self::of($date);

        return Digits::toPersian((string) $jd).' '.self::MONTHS[$jm - 1];
    }
}
