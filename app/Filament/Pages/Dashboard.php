<?php

namespace App\Filament\Pages;

use App\Models\QuoteRequest;
use App\Support\Dashboard\Metrics;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Widgets\AccountWidget;

/**
 * Panel home: business charts with filters (period, grouping, transport mode).
 * Money charts: general manager + finance. Operations charts: also sales & operations.
 * Writers see only their account card.
 */
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'داشبورد';

    public static function hasCharts(): bool
    {
        return auth()->user()?->hasRole('admin', 'finance', 'sales_ops') ?? false;
    }

    public function filtersForm(Schema $schema): Schema
    {
        if (! static::hasCharts()) {
            return $schema->components([]);
        }

        return $schema->components([
            Section::make()
                ->compact()
                ->columnSpanFull()
                ->columns(['default' => 1, 'sm' => 2, 'xl' => 5])
                ->schema([
                    Select::make('period')
                        ->label('بازه‌ی زمانی')
                        ->options(Metrics::PERIODS)
                        ->default('6m')
                        ->selectablePlaceholder(false)
                        ->native(false),
                    DatePicker::make('from')
                        ->label('از تاریخ')
                        ->native(false)
                        ->maxDate(now())
                        ->visible(fn (Get $get) => $get('period') === 'custom'),
                    DatePicker::make('to')
                        ->label('تا تاریخ')
                        ->native(false)
                        ->maxDate(now())
                        ->visible(fn (Get $get) => $get('period') === 'custom'),
                    Select::make('grouping')
                        ->label('نمایش')
                        ->options(Metrics::GROUPINGS)
                        ->default('auto')
                        ->selectablePlaceholder(false)
                        ->native(false),
                    Select::make('mode')
                        ->label('روش حمل')
                        ->options(['all' => 'همه'] + collect(QuoteRequest::TRANSPORT_MODES)->except('unsure')->all())
                        ->default('all')
                        ->selectablePlaceholder(false)
                        ->native(false),
                ]),
        ]);
    }

    /** People with charts don't need the big "Welcome / Sign out" card on top. */
    public function getWidgets(): array
    {
        $widgets = parent::getWidgets();

        return static::hasCharts()
            ? array_values(array_filter($widgets, fn ($w) => $w !== AccountWidget::class))
            : $widgets;
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
