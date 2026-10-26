<?php

namespace App\Filament\Pages\Content;

use App\Filament\Forms\MediaPicker;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class AboutContent extends ContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'درباره ما';

    protected static ?string $title = 'محتوای صفحه درباره ما';

    protected static ?string $slug = 'content/about';

    protected static function section(): string
    {
        return 'about';
    }

    protected function siteUrl(): string
    {
        return url('/about');
    }

    protected function sharedTabLabel(): string
    {
        return 'عکس‌ها، تیم و بخش‌ها';
    }

    protected function sharedFields(): array
    {
        return [
            Section::make('عکس بالای صفحه')->schema([
                MediaPicker::make('header_media')
                    ->label('عکس')
                    ->helperText('اگر خالی بماند، عکس فعلی (حیاط کانتینرها) نمایش داده می‌شود.'),
            ]),
            Section::make('تیم ما')
                ->description('ترتیب را با فلش‌ها عوض کنید. عکس عمودی (۴ به ۵) بهترین نتیجه را دارد.')
                ->schema([
                    Repeater::make('team')
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                TextInput::make('name_fa')->label('نام (فارسی)')->required()->maxLength(80),
                                TextInput::make('name_en')->label('نام (انگلیسی)')->maxLength(80)->extraInputAttributes(['dir' => 'ltr']),
                                TextInput::make('role_fa')->label('سمت (فارسی)')->maxLength(80),
                                TextInput::make('role_en')->label('سمت (انگلیسی)')->maxLength(80)->extraInputAttributes(['dir' => 'ltr']),
                            ]),
                            MediaPicker::make('photo_media')
                                ->label('عکس')
                                ->helperText(fn ($get) => filled($get('photo_default')) ? 'اگر خالی بماند، عکس فعلی این شخص نمایش داده می‌شود.' : null),
                            Hidden::make('photo_default'),
                        ])
                        ->itemLabel(fn (array $state) => $state['name_fa'] ?? null)
                        ->collapsible()
                        ->reorderableWithButtons()
                        ->addActionLabel('افزودن عضو تیم')
                        ->defaultItems(0),
                ]),
            Section::make('نمایش بخش‌ها')->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    Toggle::make('show.timeline')->label('مسیر ما (سال‌ها)'),
                    Toggle::make('show.values')->label('چه چیزی ما را متفاوت می‌کند'),
                    Toggle::make('show.team')->label('تیم ما'),
                ]),
            ]),
        ];
    }

    protected function languageFields(string $l): array
    {
        $ltr = $l === 'en' ? ['dir' => 'ltr'] : [];

        return [
            Section::make('بالای صفحه')->schema([
                $this->text($l, 'about.title', 'عنوان صفحه')->required(),
                $this->area($l, 'about.lead', 'متن معرفی کوتاه', 2, 500),
            ]),
            Section::make('داستان ما')->schema([
                $this->text($l, 'about.storyTitle', 'عنوان بخش'),
                Repeater::make($l.'.about.story')
                    ->label('پاراگراف‌ها')
                    ->simple(Textarea::make('text')->rows(4)->autosize()->required()->maxLength(3000)->extraInputAttributes($ltr))
                    ->reorderableWithButtons()
                    ->addActionLabel('افزودن پاراگراف')
                    ->defaultItems(0),
            ]),
            Section::make('مسیر ما')->collapsible()->schema([
                $this->text($l, 'about.timelineTitle', 'عنوان بخش'),
                Repeater::make($l.'.about.timeline')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('year')->label('سال')->required()->maxLength(20)->extraInputAttributes($ltr),
                        TextInput::make('text')->label('رویداد')->required()->maxLength(255)->extraInputAttributes($ltr),
                    ])
                    ->columns(['default' => 1, 'md' => 4])
                    ->reorderableWithButtons()
                    ->addActionLabel('افزودن رویداد')
                    ->defaultItems(0),
            ]),
            Section::make('چه چیزی ما را متفاوت می‌کند')->collapsible()->schema([
                $this->text($l, 'about.valuesTitle', 'عنوان بخش'),
                Repeater::make($l.'.about.values')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('title')->label('عنوان')->required()->maxLength(80)->extraInputAttributes($ltr),
                        Textarea::make('text')->label('توضیح')->required()->rows(2)->maxLength(400)->extraInputAttributes($ltr),
                    ])
                    ->maxItems(8)
                    ->reorderableWithButtons()
                    ->addActionLabel('افزودن مورد')
                    ->defaultItems(0),
            ]),
            Section::make('تیم ما')->collapsible()->schema([
                $this->text($l, 'about.teamTitle', 'عنوان بخش'),
            ]),
            $this->seo($l, 'about'),
        ];
    }
}
