<?php

namespace App\Filament\Resources\Shipments;

use App\Filament\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Filament\Resources\Shipments\Pages\ListShipments;
use App\Filament\Resources\Shipments\Pages\ViewShipment;
use App\Filament\Resources\Shipments\RelationManagers\EventsRelationManager;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\TrackingStage;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string | UnitEnum | null $navigationGroup = 'عملیات';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'محموله';

    protected static ?string $pluralModelLabel = 'محموله‌ها';

    protected static ?string $recordTitleAttribute = 'tracking_code';

    /** Transport modes without "not sure" — a real shipment always has one. */
    public static function transportModes(): array
    {
        return collect(QuoteRequest::TRANSPORT_MODES)->except('unsure')->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('نمایش به مشتری')
                ->description('این اطلاعات در صفحه‌ی رهگیری سایت به مشتری نشان داده می‌شود.')
                ->schema([
                    TextInput::make('tracking_code')
                        ->label('کد رهگیری')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('بعد از ذخیره خودکار ساخته می‌شود')
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->helperText('این کد را برای مشتری بفرستید.'),
                    Select::make('transport_mode')
                        ->label('روش حمل')
                        ->options(static::transportModes())
                        ->required()
                        ->native(false),
                    Grid::make(['default' => 1, 'sm' => 2])->schema([
                        TextInput::make('origin')->label('مبدأ')->required()->maxLength(255),
                        TextInput::make('destination')->label('مقصد')->required()->maxLength(255),
                    ]),
                    TextInput::make('cargo_description')->label('شرح بار')->maxLength(255),
                    DatePicker::make('estimated_delivery')->label('تاریخ تقریبی تحویل')->native(false),
                ]),

            Section::make('داخلی')
                ->description('مشتری این بخش را نمی‌بیند.')
                ->schema([
                    TextInput::make('customer_name')->label('نام مشتری')->required()->maxLength(255),
                    TextInput::make('customer_phone')
                        ->label('تلفن مشتری')
                        ->tel()
                        ->maxLength(32)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    Select::make('quote_request_id')
                        ->label('استعلام مرتبط')
                        ->relationship('quoteRequest', 'reference')
                        ->getOptionLabelFromRecordUsing(fn (QuoteRequest $record) => ($record->reference ?? "#{$record->id}").' — '.$record->name)
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Textarea::make('internal_notes')->label('یادداشت داخلی')->rows(4),
                ]),
        ])->columns(['default' => 1, 'lg' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('tracking_code')
                    ->label('کد رهگیری')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('کپی شد')
                    ->weight('bold'),
                TextColumn::make('customer_name')->label('مشتری')->searchable(),
                TextColumn::make('origin')->label('مسیر')
                    ->formatStateUsing(fn (Shipment $record) => "{$record->origin} ← {$record->destination}")
                    ->searchable(['origin', 'destination'])
                    ->wrap(),
                TextColumn::make('transport_mode')->label('روش حمل')
                    ->formatStateUsing(fn (string $state) => QuoteRequest::TRANSPORT_MODES[$state] ?? $state)
                    ->toggleable(),
                TextColumn::make('currentStage.name_fa')->label('وضعیت فعلی')->badge()
                    ->color(fn (Shipment $record) => $record->currentStage?->is_final ? 'success' : 'info')
                    ->placeholder('—'),
                TextColumn::make('updated_at')->label('آخرین تغییر')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('current_stage_id')
                    ->label('وضعیت')
                    ->options(fn () => TrackingStage::ordered()->pluck('name_fa', 'id')),
                SelectFilter::make('transport_mode')->label('روش حمل')->options(static::transportModes()),
            ])
            ->recordActions([
                EditAction::make()->label('مدیریت'),
                // Finance can open shipments read-only.
                ViewAction::make()->hidden(fn (Shipment $record) => static::canEdit($record)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShipments::route('/'),
            'create' => CreateShipment::route('/create'),
            'view' => ViewShipment::route('/{record}'),
            'edit' => EditShipment::route('/{record}/edit'),
        ];
    }
}
