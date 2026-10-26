<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/** Media library: upload pictures once, use them anywhere on the site. */
class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $slug = 'media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'محتوا';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'تصویر';

    protected static ?string $pluralModelLabel = 'رسانه‌ها';

    protected static ?string $navigationLabel = 'رسانه‌ها';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Image::make(fn (?Media $record) => $record?->url() ?? '', fn (?Media $record) => (string) $record?->title)
                ->imageHeight('14rem')
                ->extraAttributes(['style' => 'border-radius: .75rem; overflow: hidden']),
            Text::make(fn (?Media $record) => $record
                ? "{$record->width}×{$record->height} پیکسل · {$record->humanSize()} · ".strtoupper(str_replace('image/', '', $record->mime))
                : ''),
            TextInput::make('title')->label('عنوان (فقط برای پیدا کردن در پنل)')->maxLength(255),
            TextInput::make('alt_fa')
                ->label('متن جایگزین فارسی (alt)')
                ->helperText('یک جمله‌ی کوتاه که بگوید در عکس چه می‌بینیم. برای سئو و نابینایان مهم است.')
                ->maxLength(255),
            TextInput::make('alt_en')
                ->label('متن جایگزین انگلیسی (alt)')
                ->maxLength(255)
                ->extraInputAttributes(['dir' => 'ltr']),
            TextInput::make('url')
                ->label('آدرس فایل')
                ->formatStateUsing(fn (?Media $record) => $record?->url())
                ->readOnly()
                ->dehydrated(false)
                ->copyable(copyMessage: 'کپی شد')
                ->extraInputAttributes(['dir' => 'ltr']),
            Text::make(function (?Media $record) {
                $usages = $record?->usages() ?? [];

                return $usages === [] ? 'این تصویر هنوز جایی از سایت استفاده نشده است.' : 'استفاده شده در: '.implode('، ', $usages);
            }),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['default' => 2, 'md' => 3, 'xl' => 5])
            ->paginated([20, 40, 80])
            ->defaultPaginationPageOption(40)
            ->searchPlaceholder('جستجو در عنوان یا متن جایگزین')
            ->columns([
                Stack::make([
                    ImageColumn::make('thumb')
                        ->label('')
                        ->state(fn (Media $record) => $record->variants[0]['path'] ?? $record->path)
                        ->disk('public')
                        ->visibility('public')
                        ->imageHeight('9rem')
                        ->extraImgAttributes(['style' => 'width: 100%; object-fit: cover; border-radius: .6rem', 'loading' => 'lazy'])
                        ->extraAttributes(['style' => 'width: 100%']),
                    TextColumn::make('title')
                        ->label('عنوان')
                        ->searchable(query: fn (Builder $query, string $search) => $query->where(fn (Builder $q) => $q
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('alt_fa', 'like', "%{$search}%")
                            ->orWhere('alt_en', 'like', "%{$search}%")
                            ->orWhere('original_name', 'like', "%{$search}%")))
                        ->weight('bold')
                        ->limit(40)
                        ->placeholder('بدون عنوان'),
                    TextColumn::make('details')
                        ->state(fn (Media $record) => "{$record->width}×{$record->height} · {$record->humanSize()}")
                        ->color('gray')
                        ->size('xs'),
                    TextColumn::make('alt_missing')
                        ->state(fn (Media $record) => blank($record->alt_fa) ? 'متن جایگزین ندارد' : null)
                        ->badge()
                        ->color('warning'),
                ])->space(2),
            ])
            ->recordActions([
                EditAction::make()->label('جزئیات')->modalWidth('lg'),
                Action::make('open')
                    ->label('باز کردن')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Media $record) => $record->url())
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->modalDescription('فایل‌های این تصویر برای همیشه پاک می‌شوند.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('تصویرهایی که جایی از سایت استفاده شده‌اند پاک نمی‌شوند.')
                        ->using(function (Collection $records): void {
                            $skipped = 0;
                            foreach ($records as $record) {
                                if ($record->usages() !== []) {
                                    $skipped++;

                                    continue;
                                }
                                $record->delete();
                            }
                            if ($skipped > 0) {
                                Notification::make()
                                    ->title("{$skipped} تصویر چون جایی استفاده شده بود پاک نشد.")
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('هنوز تصویری آپلود نشده')
            ->emptyStateDescription('با دکمه‌ی «آپلود تصویر» عکس‌ها را اضافه کنید؛ خودشان برای وب بهینه می‌شوند.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMedia::route('/'),
        ];
    }
}
