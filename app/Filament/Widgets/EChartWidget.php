<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

/**
 * Base for the dashboard charts (drawn in the browser by resources/js/admin/charts.ts).
 * Subclasses return a chart config built from App\Support\Dashboard\Metrics.
 */
abstract class EChartWidget extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.echart';

    /** @var list<string> */
    protected static array $roles = ['admin', 'finance'];

    protected static bool $isLazy = false;

    abstract protected function heading(): string;

    abstract protected function config(Metrics $metrics): array;

    protected function description(Metrics $metrics): ?string
    {
        return $metrics->periodLabel();
    }

    protected function icon(): ?string
    {
        return null;
    }

    protected function emptyText(): string
    {
        return 'برای این بازه داده‌ای ثبت نشده است.';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(...static::$roles) ?? false;
    }

    protected function getViewData(): array
    {
        $metrics = Metrics::fromFilters($this->pageFilters);
        $config = $this->config($metrics);
        $empty = collect($config['series'] ?? [])->every(fn (array $s) => collect($s['data'] ?? [])->every(fn ($v) => (float) $v == 0.0));

        return [
            'heading' => $this->heading(),
            'description' => $this->description($metrics),
            'icon' => $this->icon(),
            'config' => $config,
            'empty' => $empty,
            'emptyText' => $this->emptyText(),
        ];
    }
}
