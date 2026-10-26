<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;

/** Busiest routes. Finance roles also see their sales. */
class TopRoutesChart extends EChartWidget
{
    protected static ?int $sort = 5;

    protected static array $roles = ['admin', 'finance', 'sales_ops'];

    protected function heading(): string
    {
        return auth()->user()?->hasRole('admin', 'finance') ? 'پرفروش‌ترین مسیرها' : 'پرتکرارترین مسیرها';
    }

    protected function icon(): ?string
    {
        return 'heroicon-o-map';
    }

    protected function config(Metrics $metrics): array
    {
        $money = auth()->user()?->hasRole('admin', 'finance');
        $data = $metrics->topRoutes(8, $money ? 'revenue' : 'count');

        return [
            'kind' => 'hbar',
            'format' => $money ? 'money' : 'count',
            'height' => max(240, 48 * count($data['labels']) + 60),
            'categories' => $data['labels'],
            'series' => $money
                ? [['name' => 'فروش', 'data' => $data['revenue'], 'color' => '#2f54eb']]
                : [['name' => 'تعداد محموله', 'data' => $data['count'], 'color' => '#2f54eb']],
        ];
    }
}
