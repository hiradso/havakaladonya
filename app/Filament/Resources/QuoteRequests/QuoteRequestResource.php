<?php

namespace App\Filament\Resources\QuoteRequests;

use App\Filament\Resources\QuoteRequests\Pages\EditQuoteRequest;
use App\Filament\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Models\QuoteRequest;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string | UnitEnum | null $navigationGroup = 'فروش';

    protected static ?string $modelLabel = 'استعلام قیمت';

    protected static ?string $pluralModelLabel = 'استعلام‌های قیمت';

    protected static ?string $recordTitleAttribute = 'reference';

    /** Number of unanswered requests next to the menu item. */
    public static function getNavigationBadge(): ?string
    {
        $count = QuoteRequest::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        // Requests only come from the website form.
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('پیگیری')
                ->description('این بخش فقط برای کارشناسان شرکت است و مشتری آن را نمی‌بیند.')
                ->columnSpanFull()
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        Select::make('status')
                            ->label('وضعیت')
                            ->options(QuoteRequest::STATUSES)
                            ->required()
                            ->native(false),
                        Select::make('assigned_to')
                            ->label('کارشناس مسئول')
                            ->options(fn () => User::where('is_admin', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),
                    Textarea::make('admin_notes')->label('یادداشت داخلی')->rows(4),
                ]),

            Section::make('مشتری')
                ->schema([
                    TextInput::make('name')->label('نام')->disabled(),
                    TextInput::make('company')->label('شرکت')->disabled(),
                    TextInput::make('phone')->label('تلفن')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('email')->label('ایمیل')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                ]),

            Section::make('محموله')
                ->schema([
                    Select::make('transport_mode')->label('روش حمل')->options(QuoteRequest::TRANSPORT_MODES)->disabled(),
                    TextInput::make('origin')->label('مبدأ')->disabled(),
                    TextInput::make('destination')->label('مقصد')->disabled(),
                    TextInput::make('cargo_type')->label('نوع کالا')->disabled(),
                    Grid::make(2)->schema([
                        TextInput::make('weight_kg')->label('وزن (kg)')->disabled(),
                        TextInput::make('volume_cbm')->label('حجم (m³)')->disabled(),
                    ]),
                    Select::make('load_type')->label('نوع بار')->options(QuoteRequest::LOAD_TYPES)->disabled(),
                    Grid::make(2)->schema([
                        Toggle::make('needs_clearance')->label('ترخیص')->disabled(),
                        Toggle::make('needs_insurance')->label('بیمه')->disabled(),
                    ]),
                    Textarea::make('description')->label('توضیحات مشتری')->rows(4)->disabled(),
                    TextInput::make('attachment_name')->label('فایل پیوست')->disabled()
                        ->helperText('برای دریافت، دکمه‌ی «دانلود پیوست» بالای صفحه را بزنید.'),
                ]),
        ])->columns(['default' => 1, 'lg' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->label('کد')->searchable()->weight('bold'),
                TextColumn::make('name')->label('نام')->searchable(),
                TextColumn::make('phone')->label('تلفن')->searchable()->toggleable()
                    // LRM mark keeps "+98…" from flipping to "…98+" in the right-to-left table.
                    ->formatStateUsing(fn (?string $state) => $state ? "\u{200E}{$state}" : $state),
                TextColumn::make('transport_mode')->label('روش حمل')
                    ->formatStateUsing(fn (string $state) => QuoteRequest::TRANSPORT_MODES[$state] ?? $state),
                TextColumn::make('origin')->label('مسیر')
                    ->formatStateUsing(fn (QuoteRequest $record) => "{$record->origin} ← {$record->destination}")
                    ->wrap(),
                TextColumn::make('status')->label('وضعیت')->badge()
                    ->formatStateUsing(fn (string $state) => QuoteRequest::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'in_progress' => 'info',
                        'quoted' => 'primary',
                        'won' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('assignee.name')->label('کارشناس')->toggleable(),
                TextColumn::make('created_at')->label('تاریخ')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('وضعیت')->options(QuoteRequest::STATUSES),
                SelectFilter::make('transport_mode')->label('روش حمل')->options(QuoteRequest::TRANSPORT_MODES),
            ])
            ->recordActions([
                EditAction::make()->label('بررسی'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuoteRequests::route('/'),
            'edit' => EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
