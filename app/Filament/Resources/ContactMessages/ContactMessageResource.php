<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string | UnitEnum | null $navigationGroup = 'فروش';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'پیام تماس';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false; // Messages only come from the website.
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('پیگیری')
                ->schema([
                    Select::make('status')->label('وضعیت')->options(ContactMessage::STATUSES)->required()->native(false),
                    Textarea::make('admin_notes')->label('یادداشت داخلی')->rows(3),
                ]),
            Section::make('پیام')
                ->schema([
                    TextInput::make('name')->label('نام')->disabled(),
                    TextInput::make('phone')->label('تلفن')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('email')->label('ایمیل')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                    Textarea::make('message')->label('متن پیام')->rows(6)->disabled(),
                ]),
        ])->columns(['default' => 1, 'lg' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('نام')->searchable()->weight('bold'),
                TextColumn::make('phone')->label('تلفن')->searchable()
                    ->formatStateUsing(fn (string $state) => "\u{200E}".$state),
                TextColumn::make('message')->label('پیام')->limit(60)->wrap()->toggleable(),
                TextColumn::make('status')->label('وضعیت')->badge()
                    ->formatStateUsing(fn (string $state) => ContactMessage::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'answered' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('تاریخ')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('وضعیت')->options(ContactMessage::STATUSES),
            ])
            ->recordActions([
                EditAction::make()->label('بررسی'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'edit' => EditContactMessage::route('/{record}/edit'),
        ];
    }
}
