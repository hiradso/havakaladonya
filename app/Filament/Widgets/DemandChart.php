<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;

/** Quote requests coming in vs. shipments started (everyone who handles customers). */
class DemandChart extends EChartWidget
{
    protected static ?int $sort = 4;

    protected static array $roles = ['admin', 'finance', 'sales_ops'];

    protected function heading(): string
    {
        return 'استعلام‌ها و محموله‌های جدید';
    }

    protected function icon(): ?string
    {
        return 'heroicon-o-arrow-trending-up';
    }

    protected function config(Metrics $metrics): array
    {
        $data = $metrics->demandSeries();

        return [
            'kind' => 'cartesian',
            'format' => 'count',
            'switchable' => true,
            'height' => 320,
            'categories' => $data['labels'],
            'series' => [
                ['name' => 'استعلام قیمت', 'data' => $data['quotes'], 'type' => 'line', 'area' => true, 'color' => '#ffb020'],
                ['name' => 'محموله‌ی جدید', 'data' => $data['shipments'], 'type' => 'line', 'area' => true, 'color' => '#2f54eb'],
            ],
        ];
    }
}
