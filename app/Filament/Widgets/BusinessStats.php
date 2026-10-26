<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;
use App\Support\Digits;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Headline numbers. Money for the general manager and finance; operations for everyone who handles customers. */
class BusinessStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin', 'finance', 'sales_ops') ?? false;
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $m = Metrics::fromFilters($this->pageFilters);
        $ops = $m->operationsTotals();
        $stats = [];

        if (auth()->user()?->hasRole('admin', 'finance')) {
            $f = $m->financeTotals();
            $series = $m->financeSeries();
            $stats[] = Stat::make('فروش', self::money($f['revenue']))
                ->description(self::changeText($f['revenue'] > 0 ? $f['revenueChange'] : null, Digits::toPersian(number_format($f['count'])).' فاکتور'))
                ->descriptionIcon(($f['revenueChange'] ?? 0) < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color(($f['revenueChange'] ?? 0) < 0 ? 'danger' : 'success')
                ->chart($series['revenue']);
            $stats[] = Stat::make('سود', self::money($f['profit']))
                ->description($f['margin'] === null ? 'حاشیه‌ی سود: —' : 'حاشیه‌ی سود: '.self::percent($f['margin']))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($f['profit'] < 0 ? 'danger' : 'success')
                ->chart($series['profit']);
            $stats[] = Stat::make('مطالبات وصول‌نشده', self::money($f['receivable']))
                ->description('مانده‌ی همه‌ی فاکتورها')
                ->descriptionIcon('heroicon-m-clock')
                ->color($f['receivable'] > 0 ? 'warning' : 'gray');
            $stats[] = Stat::make('میانگین هر فاکتور', self::money($f['average']))
                ->description('در این بازه')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray');
        }

        $stats[] = Stat::make('استعلام قیمت', Digits::toPersian(number_format($ops['quotes'])))
            ->description($ops['newQuotes'] > 0 ? Digits::toPersian((string) $ops['newQuotes']).' استعلام بررسی‌نشده' : 'همه بررسی شده‌اند')
            ->descriptionIcon('heroicon-m-inbox-arrow-down')
            ->color($ops['newQuotes'] > 0 ? 'warning' : 'gray')
            ->chart($m->demandSeries()['quotes']);
        $stats[] = Stat::make('نرخ تبدیل به سفارش', $ops['conversion'] === null ? '—' : self::percent($ops['conversion']))
            ->description('استعلام‌هایی که به سفارش رسیدند')
            ->descriptionIcon('heroicon-m-check-badge')
            ->color('info');
        $stats[] = Stat::make('محموله‌های در جریان', Digits::toPersian(number_format($ops['active'])))
            ->description(Digits::toPersian((string) $ops['delivered']).' تحویل‌شده در این بازه')
            ->descriptionIcon('heroicon-m-truck')
            ->color('info');
        $stats[] = Stat::make('گفتگوی آنلاین', Digits::toPersian(number_format($ops['chats'])))
            ->description('گفتگوی جدید در این بازه')
            ->descriptionIcon('heroicon-m-chat-bubble-left-right')
            ->color('gray');

        return $stats;
    }

    private static function money(int $amount): string
    {
        $abs = abs($amount);
        $text = match (true) {
            $abs >= 1_000_000_000 => round($abs / 1_000_000_000, 2).' میلیارد',
            $abs >= 1_000_000 => round($abs / 1_000_000, 1).' میلیون',
            default => number_format($abs),
        };

        return ($amount < 0 ? '−' : '').str_replace('.', '٫', Digits::toPersian($text)).' تومان';
    }

    private static function percent(float $value): string
    {
        return str_replace('.', '٫', Digits::toPersian((string) $value)).'٪';
    }

    private static function changeText(?float $change, string $fallback): string
    {
        if ($change === null) {
            return $fallback;
        }

        return ($change >= 0 ? '+' : '−').self::percent(abs($change)).' نسبت به دوره‌ی قبل';
    }
}
