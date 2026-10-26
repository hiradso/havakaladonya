<?php

namespace App\Filament\Pages\Content;

use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class FooterContent extends ContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'فوتر';

    protected static ?string $title = 'محتوای فوتر';

    protected static ?string $slug = 'content/footer';

    protected static function section(): string
    {
        return 'footer';
    }

    protected function siteUrl(): string
    {
        return url('/').'#footer';
    }

    protected function languageFields(string $l): array
    {
        return [
            Section::make('فوتر')
                ->description('نشانی، تلفن و ایمیل فوتر از صفحه‌ی «تماس با ما و اطلاعات تماس» خوانده می‌شود.')
                ->schema([
                    $this->area($l, 'footer.about', 'معرفی کوتاه شرکت', 2, 300),
                    $this->text($l, 'footer.rights', 'متن کپی‌رایت'),
                ]),
        ];
    }
}
