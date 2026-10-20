<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Support\Roles;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Staff accounts of the admin panel (general manager only, see UserPolicy). */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string | UnitEnum | null $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'کاربر پنل';

    protected static ?string $pluralModelLabel = 'کاربران پنل';

    protected static ?string $recordTitleAttribute = 'name';

    /** Only staff accounts are listed here (future customer accounts have no role). */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('role');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->label('نام')->required()->maxLength(120),
                TextInput::make('email')
                    ->label('ایمیل (برای ورود)')
                    ->email()
                    ->required()
                    ->maxLength(150)
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(['dir' => 'ltr']),
                Select::make('role')
                    ->label('نقش')
                    ->options(Roles::LABELS)
                    ->required()
                    ->native(false)
                    // You can't take away your own manager role (so the panel never ends up without one).
                    ->disabled(fn (?User $record) => $record?->is(auth()->user()))
                    ->dehydrated(fn (?User $record) => ! $record?->is(auth()->user()))
                    ->helperText(fn (?User $record) => $record?->is(auth()->user()) ? 'نقش خودتان را نمی‌توانید تغییر دهید.' : null),
                TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(100)
                    // Required for new users; on edit, empty means "keep the current password".
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'برای تغییر رمز پر کنید؛ خالی بماند رمز قبلی می‌ماند.' : 'حداقل ۸ حرف.'),
            ])->columns(['default' => 1, 'md' => 2]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('نام')->searchable()->weight('bold'),
                TextColumn::make('email')->label('ایمیل')->searchable(),
                TextColumn::make('role')->label('نقش')->badge()
                    ->formatStateUsing(fn (?string $state) => Roles::LABELS[$state] ?? '—')
                    ->color(fn (?string $state) => match ($state) {
                        Roles::ADMIN => 'danger',
                        Roles::SALES_OPS => 'primary',
                        Roles::FINANCE => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('ایجاد')->jalali()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('role')->label('نقش')->options(Roles::LABELS),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
