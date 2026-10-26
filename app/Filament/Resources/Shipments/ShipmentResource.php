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
use App\Support\Digits;
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
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'عملیات';

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
                // Finance only fills in the money section; the rest stays read-only for them.
                ->disabled(fn () => static::financeOnly())
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

            Section::make('مالی')
                ->description('فقط مدیر کل و مالی این بخش را می‌بینند. مبالغ به تومان؛ سود و مانده خودکار حساب می‌شود.')
                ->icon('heroicon-o-banknotes')
                ->visible(fn () => auth()->user()?->hasRole(...Shipment::FINANCE_ROLES))
                ->columnSpanFull()
                ->schema([
                    Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                        static::moneyInput('invoice_amount', 'مبلغ فاکتور (فروش)'),
                        static::moneyInput('cost_amount', 'هزینه‌ی تمام‌شده'),
                        static::moneyInput('paid_amount', 'دریافتی تا امروز')->default(0),
                        DatePicker::make('invoiced_at')
                            ->label('تاریخ فاکتور')
                            ->native(false)
                            ->helperText('خالی بماند، تاریخ امروز ثبت می‌شود.'),
                    ]),
                    Text::make(function (Get $get): string {
                        $invoice = (int) static::moneyValue($get('invoice_amount'));
                        if ($invoice === 0) {
                            return 'هنوز فاکتوری ثبت نشده است.';
                        }
                        $profit = $invoice - (int) static::moneyValue($get('cost_amount'));
                        $balance = max(0, $invoice - (int) static::moneyValue($get('paid_amount')));
                        $margin = round($profit / $invoice * 100, 1);

                        return Digits::toPersian('سود: '.number_format($profit).' تومان ('.$margin.'٪) · مانده‌ی دریافت: '.number_format($balance).' تومان');
                    }),
                ]),

            Section::make('داخلی')
                ->description('مشتری این بخش را نمی‌بیند.')
                ->disabled(fn () => static::financeOnly())
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

    /** The finance role: may edit money, nothing else. */
    public static function financeOnly(): bool
    {
        return auth()->user()?->hasRole('finance') ?? false;
    }

    private static function moneyInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->suffix('تومان')
            ->inputMode('numeric')
            ->live(onBlur: true)
            ->extraInputAttributes(['dir' => 'ltr'])
            ->mask(RawJs::make('$money($input)'))
            ->stripCharacters(',')
            ->dehydrateStateUsing(fn ($state) => static::moneyValue($state))
            ->rule(fn () => function (string $attribute, $value, \Closure $fail): void {
                if ($value !== null && $value !== '' && static::moneyValue($value) === null) {
                    $fail('فقط عدد وارد کنید.');
                }
            });
    }

    /** "12,500,000" or "۱۲٬۵۰۰٬۰۰۰" → 12500000; empty → null. */
    public static function moneyValue(mixed $value): ?int
    {
        $digits = preg_replace('/\D+/', '', Digits::toLatin((string) $value)) ?? '';

        return $digits === '' ? null : (int) $digits;
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
                TextColumn::make('invoice_amount')->label('فاکتور (تومان)')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—')
                    ->visible(fn () => auth()->user()?->hasRole(...Shipment::FINANCE_ROLES))
                    ->toggleable(),
                TextColumn::make('profit')->label('سود (تومان)')
                    ->state(fn (Shipment $record) => $record->profit())
                    ->numeric()
                    ->color(fn ($state) => $state !== null && $state < 0 ? 'danger' : 'success')
                    ->placeholder('—')
                    ->visible(fn () => auth()->user()?->hasRole(...Shipment::FINANCE_ROLES))
                    ->toggleable(),
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
