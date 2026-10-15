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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AppearanceSettings extends Page
{
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
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('رنگ اصلی')
                    ->description('این رنگ روی دکمه‌ها، لینک‌ها و بخش‌های تأکیدی سایت و همین پنل اعمال می‌شود. رنگ متن روی آن (تیره یا روشن) خودکار انتخاب می‌شود.')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('رنگ اصلی قالب')
                            ->hex()
                            ->required()
                            ->regex('/^#[0-9a-fA-F]{6}$/')
                            ->helperText('پیش‌فرض: '.Theme::DEFAULT_PRIMARY),
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
                                ->label('بازگشت به رنگ پیش‌فرض')
                                ->color('gray')
                                ->requiresConfirmation()
                                ->action(fn () => $this->saveColor(Theme::DEFAULT_PRIMARY)),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $this->saveColor($this->form->getState()['primary_color']);
    }

    protected function saveColor(string $color): void
    {
        Setting::set('primary_color', strtoupper($color));

        Notification::make()
            ->success()
            ->title('رنگ قالب ذخیره شد')
            ->send();

        // Reload so the panel picks up the new color immediately.
        $this->redirect(static::getUrl());
    }
}
