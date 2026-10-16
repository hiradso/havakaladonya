<?php

namespace App\Filament\Resources\TrackingStages;

use App\Filament\Resources\TrackingStages\Pages\ManageTrackingStages;
use App\Models\ShipmentEvent;
use App\Models\TrackingStage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class TrackingStageResource extends Resource
{
    protected static ?string $model = TrackingStage::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string | UnitEnum | null $navigationGroup = 'عملیات';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'مرحله‌ی رهگیری';

    protected static ?string $pluralModelLabel = 'مراحل رهگیری';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name_fa')->label('نام فارسی')->required()->maxLength(255),
            TextInput::make('name_en')->label('نام انگلیسی')->required()->maxLength(255)
                ->extraInputAttributes(['dir' => 'ltr']),
            Toggle::make('is_final')->label('مرحله‌ی پایانی (تحویل شد)'),
            Toggle::make('is_active')->label('فعال')->default(true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->paginated(false)
            ->description('ترتیب را با کشیدن ردیف‌ها (دکمه‌ی بالای جدول) عوض کنید. مرحله‌ای که استفاده شده حذف نمی‌شود؛ غیرفعالش کنید.')
            ->columns([
                TextColumn::make('name_fa')->label('نام فارسی')->weight('bold'),
                TextColumn::make('name_en')->label('نام انگلیسی'),
                IconColumn::make('is_final')->label('پایانی')->boolean(),
                ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (TrackingStage $record) => ShipmentEvent::where('tracking_stage_id', $record->id)->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTrackingStages::route('/'),
        ];
    }
}
