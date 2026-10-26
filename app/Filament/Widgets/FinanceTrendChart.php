<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;

/** Sales, cost and profit over time (general manager + finance). */
class FinanceTrendChart extends EChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function heading(): string
    {
        return 'فروش، هزینه و سود';
    }

    protected function icon(): ?string
    {
        return 'heroicon-o-presentation-chart-line';
    }

    protected function emptyText(): string
    {
        return 'هنوز برای محموله‌های این بازه فاکتور ثبت نشده است. مبلغ فاکتور و هزینه را در بخش «مالی» هر محموله وارد کنید.';
    }

    protected function config(Metrics $metrics): array
    {
        $data = $metrics->financeSeries();

        return [
            'kind' => 'cartesian',
            'format' => 'money',
            'switchable' => true,
            'height' => 360,
            'categories' => $data['labels'],
            'series' => [
                ['name' => 'فروش', 'data' => $data['revenue'], 'color' => '#2f54eb'],
                ['name' => 'هزینه', 'data' => $data['cost'], 'color' => '#94a3b8'],
                ['name' => 'سود', 'data' => $data['profit'], 'type' => 'line', 'color' => '#10b981', 'area' => true],
            ],
        ];
    }
}
