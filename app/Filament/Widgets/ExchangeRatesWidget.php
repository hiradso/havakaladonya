<?php

namespace App\Filament\Widgets;

use App\Support\Digits;
use App\Support\Rates\ExchangeRates;
use App\Support\Roles;
use Carbon\CarbonImmutable;
use Filament\Widgets\Widget;

/**
 * Free-market currency rates in toman at the top of the dashboard, for every panel role.
 * Reads our own database only (filled by `rates:update` every 5 minutes), so it needs no VPN
 * and costs nothing on page load; it re-reads every minute while the page is open.
 */
class ExchangeRatesWidget extends Widget
{
    protected string $view = 'filament.widgets.exchange-rates';

    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(...array_keys(Roles::LABELS)) ?? false;
    }

    protected function getViewData(): array
    {
        $data = ExchangeRates::forDashboard();

        return [
            'rates' => array_map(fn (array $r) => $r + [
                'priceText' => self::toman($r['price']),
                'changeText' => $r['change'] === null ? null : self::toman(abs($r['change'])),
                'percentText' => $r['percent'] === null ? null : Digits::toPersian(str_replace('.', '٫', number_format(abs($r['percent']), 2))).'٪',
                'direction' => ($r['change'] ?? 0) <=> 0,
                'rangeText' => $r['high'] && $r['low']
                    ? 'کمترین امروز: '.self::toman($r['low']).' — بیشترین: '.self::toman($r['high'])
                    : null,
                'quotedText' => $r['quotedAt'] ? 'آخرین قیمت در منبع: '.self::clock($r['quotedAt']) : null,
                'spark' => self::sparkline($r['trend']),
            ], $data['rates']),
            'stale' => $data['stale'],
            'updatedText' => $data['fetchedAt'] ? self::ago($data['fetchedAt']) : null,
            'updatedClock' => $data['fetchedAt'] ? self::clock($data['fetchedAt']) : null,
            'sourceText' => $data['source'] ? ExchangeRates::SOURCES[$data['source']] ?? $data['source'] : null,
        ];
    }

    public static function toman(float $value): string
    {
        $decimals = $value < 100 ? 1 : 0;

        return Digits::toPersian(number_format($value, $decimals));
    }

    public static function clock(CarbonImmutable $time): string
    {
        return Digits::toPersian($time->setTimezone('Asia/Tehran')->format('H:i'));
    }

    public static function ago(CarbonImmutable $time): string
    {
        $minutes = (int) floor($time->diffInMinutes(now(), true));

        return Digits::toPersian(match (true) {
            $minutes < 1 => 'همین الان',
            $minutes < 60 => $minutes.' دقیقه پیش',
            $minutes < 1440 => intdiv($minutes, 60).' ساعت پیش',
            default => intdiv($minutes, 1440).' روز پیش',
        });
    }

    /**
     * SVG polyline points (100×28 box) for the 7-day trend, or null with too few points.
     *
     * @param  list<float>  $values
     */
    public static function sparkline(array $values): ?array
    {
        $values = array_values($values);
        // Keep at most ~60 points so the line stays smooth and the page light.
        $n = count($values);
        if ($n > 60) {
            $values = array_map(fn ($i) => $values[(int) round($i * ($n - 1) / 59)], range(0, 59));
        }
        if (count($values) < 2) {
            return null;
        }

        $min = min($values);
        $max = max($values);
        $span = $max - $min ?: 1;
        $last = count($values) - 1;
        $points = [];
        foreach ($values as $i => $v) {
            $points[] = round($i / $last * 100, 2).','.round(26 - ($v - $min) / $span * 24, 2);
        }

        return [
            'line' => implode(' ', $points),
            'area' => '0,28 '.implode(' ', $points).' 100,28',
            'up' => end($values) >= $values[0],
        ];
    }
}
