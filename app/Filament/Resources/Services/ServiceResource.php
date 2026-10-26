<?php

namespace App\Filament\Resources\Services;

use App\Filament\Forms\MediaPicker;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Service pages (air freight, customs clearance, ...). Persian and English in tabs. */
class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'خدمت';

    protected static ?string $pluralModelLabel = 'خدمات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('service')->persistTabInQueryString()->columnSpanFull()->tabs([
                Tab::make('متن فارسی')->icon('heroicon-o-language')->schema(self::languageFields('fa')),
                Tab::make('English')->icon('heroicon-o-globe-alt')->schema(self::languageFields('en')),
                Tab::make('تنظیمات و عکس')->icon('heroicon-o-adjustments-horizontal')->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        TextInput::make('slug')
                            ->label('آدرس صفحه (slug)')
                            ->prefix('/services/')
                            ->maxLength(120)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->disabledOn('edit')
                            ->helperText(fn (string $operation) => $operation === 'edit'
                                ? 'آدرس خدمت‌های موجود عوض نمی‌شود تا لینک‌ها در گوگل خراب نشوند.'
                                : 'خالی بماند، از عنوان انگلیسی ساخته می‌شود. بعد از ذخیره قابل تغییر نیست.'),
                        Select::make('icon')
                            ->label('آیکون')
                            ->options(Service::ICONS)
                            ->default('package')
                            ->required()
                            ->native(false),
                        Select::make('quote_mode')
                            ->label('روش حمل پیش‌فرض در فرم استعلام')
                            ->options(Service::QUOTE_MODES)
                            ->placeholder('هیچ‌کدام')
                            ->native(false),
                        Toggle::make('is_active')->label('نمایش در سایت')->default(true)->inline(false),
                    ]),
                    MediaPicker::make('media_id')
                        ->label('عکس صفحه')
                        ->helperText(fn ($get) => filled($get('photo_default'))
                            ? 'اگر خالی بماند، عکس فعلی این خدمت نمایش داده می‌شود.'
                            : 'اگر خالی بماند، به‌جای عکس آیکون خدمت نمایش داده می‌شود.'),
                ]),
            ]),
        ]);
    }

    private static function languageFields(string $l): array
    {
        $ltr = $l === 'en' ? ['dir' => 'ltr'] : [];
        $text = fn (string $name, string $label, int $max = 255) => TextInput::make("content.{$l}.{$name}")->label($label)->maxLength($max)->extraInputAttributes($ltr);
        $area = fn (string $name, string $label, int $rows = 3, int $max = 2000) => Textarea::make("content.{$l}.{$name}")->label($label)->rows($rows)->autosize()->maxLength($max)->extraInputAttributes($ltr);

        return [
            Section::make('معرفی')->schema([
                $text('title', 'عنوان خدمت')->required($l === 'fa'),
                $text('short', 'یک خط توضیح (در فهرست خدمات)', 200),
                $area('lead', 'متن معرفی بالای صفحه', 2, 500),
            ]),
            Section::make('متن صفحه')->schema([
                Repeater::make("content.{$l}.body")
                    ->label('پاراگراف‌ها')
                    ->simple(Textarea::make('text')->rows(4)->autosize()->required()->maxLength(4000)->extraInputAttributes($ltr))
                    ->reorderableWithButtons()
                    ->addActionLabel('افزودن پاراگراف')
                    ->defaultItems(0),
                Repeater::make("content.{$l}.features")
                    ->label('«آنچه انجام می‌دهیم» (فهرست تیک‌دار)')
                    ->simple(TextInput::make('text')->required()->maxLength(200)->extraInputAttributes($ltr))
                    ->reorderableWithButtons()
                    ->addActionLabel('افزودن مورد')
                    ->defaultItems(0),
            ]),
            Section::make('فهرست‌های اضافه (اختیاری)')
                ->description('مثلاً «مدارک لازم برای ترخیص». خالی بمانند نمایش داده نمی‌شوند.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Repeater::make("content.{$l}.groups")
                        ->label('فهرست‌های شماره‌دار')
                        ->schema([
                            TextInput::make('title')->label('عنوان فهرست')->required()->maxLength(120)->extraInputAttributes($ltr),
                            Repeater::make('items')
                                ->label('موارد')
                                ->simple(TextInput::make('text')->required()->maxLength(300)->extraInputAttributes($ltr))
                                ->reorderableWithButtons()
                                ->addActionLabel('افزودن مورد')
                                ->defaultItems(1),
                        ])
                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                        ->collapsible()
                        ->addActionLabel('افزودن فهرست')
                        ->defaultItems(0),
                    $text('chips.title', 'عنوان برچسب‌ها (مثلاً «کشورهای مقصد»)', 120),
                    TagsInput::make("content.{$l}.chips.items")
                        ->label('برچسب‌ها')
                        ->placeholder('بنویسید و Enter بزنید')
                        ->reorderable(),
                ]),
            Section::make('سئو (نتایج گوگل)')
                ->description('خالی بماند، از عنوان و متن معرفی استفاده می‌شود.')
                ->collapsible()
                ->collapsed()
                ->schema([
                    $text('metaTitle', 'عنوان صفحه در گوگل', 70),
                    $area('metaDescription', 'توضیح صفحه در گوگل', 3, 320),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->paginated(false)
            ->description('ترتیب نمایش در سایت را با دکمه‌ی بالای جدول و کشیدن ردیف‌ها عوض کنید.')
            ->columns([
                ImageColumn::make('thumb')
                    ->label('')
                    ->state(fn (Service $record) => $record->media
                        ? ($record->media->variants[0]['path'] ?? $record->media->path)
                        : null)
                    ->disk('public')
                    ->imageWidth(64)
                    ->imageHeight(40)
                    ->extraImgAttributes(['style' => 'object-fit: cover; border-radius: .4rem']),
                TextColumn::make('content.fa.title')->label('عنوان')->weight('bold')->searchable(),
                TextColumn::make('content.fa.short')->label('توضیح')->color('gray')->limit(60)->wrap(),
                TextColumn::make('slug')->label('آدرس')->prefix('/services/')->color('gray')->size('xs'),
                ToggleColumn::make('is_active')->label('نمایش'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('مشاهده')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Service $record) => url('/services/'.$record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Service $record) => $record->is_active),
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('صفحه‌ی این خدمت از سایت حذف می‌شود. اگر فقط می‌خواهید موقتاً نمایش داده نشود، «نمایش» را خاموش کنید.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
