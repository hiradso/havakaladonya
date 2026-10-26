<?php

namespace App\Filament\Pages\Content;

use App\Filament\Forms\MediaPicker;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class HomeContent extends ContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'صفحه اصلی';

    protected static ?string $title = 'محتوای صفحه اصلی';

    protected static ?string $slug = 'content/home';

    protected static function section(): string
    {
        return 'home';
    }

    protected function siteUrl(): string
    {
        return url('/');
    }

    protected function sharedFields(): array
    {
        return [
            Section::make('عکس بنر')
                ->description('عکس بزرگ زیر عنوان اصلی. بهتر است افقی و حداقل ۱۶۰۰ پیکسل عرض داشته باشد.')
                ->schema([
                    MediaPicker::make('hero_media')
                        ->label('عکس')
                        ->helperText('اگر خالی بماند، عکس فعلی سایت (بندر و هواپیما) نمایش داده می‌شود.'),
                ]),
            Section::make('نمایش بخش‌ها')
                ->description('بخش‌هایی که خاموش شوند در صفحه اصلی نمایش داده نمی‌شوند.')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 3])->schema([
                        Toggle::make('show.routes')->label('نقشه‌ی مسیرها'),
                        Toggle::make('show.services')->label('فهرست خدمات'),
                        Toggle::make('show.stats')->label('نوار آمار'),
                        Toggle::make('show.blog')->label('آخرین مقالات'),
                        Toggle::make('show.cta')->label('دعوت به استعلام قیمت'),
                    ]),
                ]),
        ];
    }

    protected function languageFields(string $l): array
    {
        $ltr = $l === 'en' ? ['dir' => 'ltr'] : [];

        return [
            Section::make('بنر اصلی')->schema([
                Grid::make(['default' => 1, 'md' => 2])->schema([
                    $this->text($l, 'home.heroTitleA', 'عنوان، خط اول')->required(),
                    $this->text($l, 'home.heroTitleB', 'عنوان، خط دوم (رنگی)'),
                ]),
                $this->area($l, 'home.heroText', 'متن زیر عنوان', 2, 400),
                $this->text($l, 'home.photoAlt', 'توضیح عکس بنر (alt)')
                    ->helperText('برای سئو؛ بگویید در عکس چه دیده می‌شود.'),
            ]),
            Section::make('عنوان بخش‌ها')->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    $this->text($l, 'home.routesTitle', 'نقشه‌ی مسیرها'),
                    $this->text($l, 'home.servicesTitle', 'خدمات'),
                    $this->text($l, 'home.blogTitle', 'مقالات'),
                ]),
            ]),
            Section::make('نوار آمار')
                ->description('اعداد در سایت فارسی خودکار با ارقام فارسی نمایش داده می‌شوند.')
                ->schema([
                    Repeater::make($l.'.home.stats')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('value')->label('عدد')->required()->maxLength(20)->extraInputAttributes($ltr),
                            TextInput::make('label')->label('توضیح')->required()->maxLength(60)->extraInputAttributes($ltr),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->maxItems(6)
                        ->reorderableWithButtons()
                        ->addActionLabel('افزودن آمار')
                        ->defaultItems(0),
                ]),
            Section::make('دعوت به استعلام قیمت')->schema([
                $this->text($l, 'home.ctaTitle', 'عنوان'),
                $this->area($l, 'home.ctaText', 'متن', 2, 400),
                $this->text($l, 'home.ctaButton', 'متن دکمه', 40),
            ]),
            $this->seo($l, 'home'),
        ];
    }
}
