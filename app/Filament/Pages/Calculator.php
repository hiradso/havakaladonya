<?php

namespace App\Filament\Pages;

use App\Support\Roles;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Calculation tools for the team, all done in the browser (nothing is saved on the server):
 * precise calculator, chargeable weight, container fit, pricing and unit conversion.
 * Open to every panel role.
 */
class Calculator extends Page
{
    protected string $view = 'filament.pages.calculator';

    protected static ?string $slug = 'calculator';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string | UnitEnum | null $navigationGroup = 'ابزارها';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'ابزار محاسبه';

    protected static ?string $title = 'ابزار محاسبه';

    protected Width | string | null $maxContentWidth = Width::Full;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(...array_keys(Roles::LABELS)) ?? false;
    }
}
