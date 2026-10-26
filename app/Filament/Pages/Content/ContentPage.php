<?php

namespace App\Filament\Pages\Content;

use App\Support\SiteContent;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Base for the "Site content" pages: one form per section of the website, Persian and English
 * side by side, saved with App\Support\SiteContent. Only the general manager may edit.
 */
abstract class ContentPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    /** Key in SiteContent::SECTIONS. */
    abstract protected static function section(): string;

    /** Fields for one language ('fa' or 'en'); names are relative, e.g. "home.heroTitleA". */
    abstract protected function languageFields(string $locale): array;

    /** Fields that are the same in both languages (pictures, phone numbers, on/off switches). */
    protected function sharedFields(): array
    {
        return [];
    }

    /** Address of this page on the website, for the "view on site" button. */
    abstract protected function siteUrl(): string;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteContent::get(static::section()));
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [
            Tab::make('متن فارسی')->icon('heroicon-o-language')->schema($this->languageFields('fa')),
            Tab::make('English')->icon('heroicon-o-globe-alt')->schema($this->languageFields('en')),
        ];
        if ($shared = $this->sharedFields()) {
            array_unshift($tabs, Tab::make($this->sharedTabLabel())->icon('heroicon-o-adjustments-horizontal')->schema($shared));
        }

        return $schema
            ->components([Tabs::make('content')->tabs($tabs)->persistTabInQueryString()])
            ->statePath('data');
    }

    protected function sharedTabLabel(): string
    {
        return 'عکس‌ها و تنظیمات';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('ذخیره')->submit('save'),
                        Action::make('view')
                            ->label('مشاهده در سایت')
                            ->color('gray')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $this->siteUrl())
                            ->openUrlInNewTab(),
                        Action::make('reset')
                            ->label('بازگشت به متن پیش‌فرض')
                            ->color('danger')
                            ->link()
                            ->requiresConfirmation()
                            ->modalDescription('همه‌ی تغییرات این بخش پاک می‌شود و متن‌ها و عکس‌های اولیه‌ی سایت برمی‌گردد.')
                            ->action(function (): void {
                                SiteContent::reset(static::section());
                                $this->form->fill(SiteContent::get(static::section()));
                                Notification::make()->success()->title('متن‌های پیش‌فرض برگشت.')->send();
                            }),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        SiteContent::save(static::section(), $this->form->getState());

        Notification::make()->success()->title('ذخیره شد و روی سایت اعمال شد.')->send();
    }

    /* ---------- small helpers for the subclasses ---------- */

    protected function text(string $locale, string $name, string $label, int $max = 255): TextInput
    {
        $field = TextInput::make($locale.'.'.$name)->label($label)->maxLength($max);

        return $locale === 'en' ? $field->extraInputAttributes(['dir' => 'ltr']) : $field;
    }

    protected function area(string $locale, string $name, string $label, int $rows = 3, int $max = 2000): Textarea
    {
        $field = Textarea::make($locale.'.'.$name)->label($label)->rows($rows)->maxLength($max)->autosize();

        return $locale === 'en' ? $field->extraInputAttributes(['dir' => 'ltr']) : $field;
    }

    protected function seo(string $locale, string $ns): Section
    {
        return Section::make('سئو (نتایج گوگل)')
            ->collapsible()
            ->collapsed()
            ->schema([
                $this->text($locale, "{$ns}.metaTitle", 'عنوان صفحه در گوگل', 70)->helperText('بهتر است کمتر از ۶۰ حرف باشد.'),
                $this->area($locale, "{$ns}.metaDescription", 'توضیح صفحه در گوگل', 3, 320)->helperText('بهتر است بین ۱۲۰ تا ۱۶۰ حرف باشد.'),
            ]);
    }
}
