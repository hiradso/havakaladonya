<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;

/** Which transport mode brings the profit (general manager + finance). */
class ProfitByModeChart extends EChartWidget
{
    protected static ?int $sort = 3;

    protected function heading(): string
    {
        return 'سود به تفکیک روش حمل';
    }

    protected function icon(): ?string
    {
        return 'heroicon-o-chart-pie';
    }

    protected function config(Metrics $metrics): array
    {
        $data = $metrics->financeByMode();

        return [
            'kind' => 'donut',
            'format' => 'money',
            'height' => 320,
            'categories' => $data['labels'],
            // A donut can't show losses; negative profit counts as zero here (see the trend chart for details).
            'series' => [['name' => 'سود', 'data' => array_map(fn ($v) => max(0, $v), $data['profit'])]],
        ];
    }
}
