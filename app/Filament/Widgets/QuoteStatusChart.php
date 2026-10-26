<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\Metrics;

/** How far quote requests got: new → quoted → won / lost. */
class QuoteStatusChart extends EChartWidget
{
    protected static ?int $sort = 6;

    protected static array $roles = ['admin', 'finance', 'sales_ops'];

    protected function heading(): string
    {
        return 'وضعیت استعلام‌ها';
    }

    protected function icon(): ?string
    {
        return 'heroicon-o-funnel';
    }

    protected function config(Metrics $metrics): array
    {
        $data = $metrics->quoteStatuses();

        return [
            'kind' => 'donut',
            'format' => 'count',
            'height' => 320,
            'categories' => $data['labels'],
            'series' => [['name' => 'استعلام', 'data' => $data['values']]],
        ];
    }
}
