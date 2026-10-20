<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Theme;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AppearanceSettings extends Page
{
    /** Settings are for the general manager only. */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string | UnitEnum | null $navigationGroup = 'تنظیمات';

    protected static ?string $navigationLabel = 'ظاهر و رنگ قالب';

    protected static ?string $title = 'ظاهر و رنگ قالب';

    protected static ?string $slug = 'settings/appearance';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'primary_color' => Theme::primary(),
            'secondary_color' => Theme::secondary(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $hex = fn (string $name, string $label, string $default, string $help) => ColorPicker::make($name)
            ->label($label)
            ->hex()
            ->extraInputAttributes(['dir' => 'ltr'])
            ->required()
            ->regex('/^#[0-9a-fA-F]{6}$/')
            ->helperText($help.' — پیش‌فرض: '.$default);

        return $schema
            ->components([
                Section::make('رنگ‌های قالب')
                    ->description('این رنگ‌ها روی سایت و همین پنل اعمال می‌شوند. رنگ متن روی آن‌ها (تیره یا روشن) خودکار انتخاب می‌شود. پیش‌فرض‌ها از روی لوگو انتخاب شده‌اند.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            $hex('primary_color', 'رنگ اصلی', Theme::DEFAULT_PRIMARY, 'دکمه‌ها، تیترهای رنگی و بخش‌های تأکیدی'),
                            $hex('secondary_color', 'رنگ دوم', Theme::DEFAULT_SECONDARY, 'لینک‌ها، مسیرهای هوایی و دریایی و نشان‌های اطلاعاتی'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('ذخیره')
                                ->submit('save'),
                            Action::make('reset')
                                ->label('بازگشت به رنگ‌های لوگو')
                                ->color('gray')
                                ->requiresConfirmation()
                                ->action(fn () => $this->saveColors(Theme::DEFAULT_PRIMARY, Theme::DEFAULT_SECONDARY)),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $this->saveColors($state['primary_color'], $state['secondary_color']);
    }

    protected function saveColors(string $primary, string $secondary): void
    {
        Setting::set('primary_color', strtoupper($primary));
        Setting::set('secondary_color', strtoupper($secondary));

        Notification::make()
            ->success()
            ->title('رنگ‌های قالب ذخیره شد')
            ->send();

        // Reload so the panel picks up the new colors immediately.
        $this->redirect(static::getUrl());
    }
}
