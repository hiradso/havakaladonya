<?php

namespace App\Filament\Pages\Content;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ContactContent extends ContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'تماس با ما و اطلاعات تماس';

    protected static ?string $title = 'تماس با ما و اطلاعات تماس';

    protected static ?string $slug = 'content/contact';

    protected static function section(): string
    {
        return 'contact';
    }

    protected function siteUrl(): string
    {
        return url('/contact');
    }

    protected function sharedTabLabel(): string
    {
        return 'شماره‌ها و شبکه‌های اجتماعی';
    }

    protected function sharedFields(): array
    {
        $ltr = ['dir' => 'ltr'];

        return [
            Section::make('تلفن‌ها')
                ->description('همین شماره‌ها در هدر، فوتر، صفحه‌ی تماس، صفحه‌ی خدمات و دکمه‌ی گفتگو استفاده می‌شوند. با فاصله بنویسید تا خوانا باشد؛ لینک تماس خودکار ساخته می‌شود.')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        TextInput::make('phone')->label('تلفن دفتر')->required()->maxLength(30)->extraInputAttributes($ltr)->placeholder('021 8801 5232'),
                        TextInput::make('mobile')->label('موبایل')->maxLength(30)->extraInputAttributes($ltr),
                        TextInput::make('whatsapp')->label('شماره‌ی واتس‌اپ')->maxLength(30)->extraInputAttributes($ltr)
                            ->helperText('خالی بماند، دکمه‌ی واتس‌اپ نمایش داده نمی‌شود.'),
                        TextInput::make('fax')->label('فکس')->maxLength(30)->extraInputAttributes($ltr),
                    ]),
                ]),
            Section::make('ایمیل، نقشه و شبکه‌های اجتماعی')
                ->description('هر کدام خالی بماند، در سایت نمایش داده نمی‌شود.')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        TextInput::make('email')->label('ایمیل')->email()->required()->maxLength(120)->extraInputAttributes($ltr),
                        TextInput::make('instagram')->label('اینستاگرام (نام کاربری)')->prefix('@')->maxLength(60)->extraInputAttributes($ltr),
                        TextInput::make('telegram')->label('تلگرام (نام کاربری)')->prefix('@')->maxLength(60)->extraInputAttributes($ltr),
                        TextInput::make('linkedin')->label('لینکدین (آدرس کامل)')->url()->maxLength(255)->extraInputAttributes($ltr),
                    ]),
                    TextInput::make('map_url')
                        ->label('لینک نقشه (مسیریابی)')
                        ->url()
                        ->maxLength(1000)
                        ->extraInputAttributes($ltr)
                        ->helperText('لینک اشتراک‌گذاری مکان دفتر از گوگل‌مپ یا نشان را اینجا بچسبانید.'),
                ]),
        ];
    }

    protected function languageFields(string $l): array
    {
        return [
            Section::make('نشانی و ساعت کاری')
                ->description('نشانی در فوتر و صفحه‌ی تماس نمایش داده می‌شود.')
                ->schema([
                    $this->area($l, 'footer.address', 'نشانی دفتر', 2, 300)->required(),
                    $this->text($l, 'contact.hours', 'ساعت کاری')->placeholder($l === 'fa' ? 'شنبه تا چهارشنبه، ۹ تا ۱۷' : 'Sat–Wed, 9:00–17:00')
                        ->helperText('اختیاری؛ خالی بماند نمایش داده نمی‌شود.'),
                ]),
            Section::make('صفحه‌ی تماس با ما')->schema([
                $this->text($l, 'contact.title', 'عنوان صفحه')->required(),
                $this->area($l, 'contact.lead', 'متن زیر عنوان', 2, 400),
                $this->text($l, 'contact.formTitle', 'عنوان فرم پیام'),
                $this->area($l, 'contact.formText', 'متن بالای فرم', 2, 300),
            ]),
            $this->seo($l, 'contact'),
        ];
    }
}
