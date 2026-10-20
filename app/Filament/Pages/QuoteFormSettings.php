<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\QuoteForm;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class QuoteFormSettings extends Page
{
    /** Settings are for the general manager only. */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string | UnitEnum | null $navigationGroup = 'تنظیمات';

    protected static ?string $navigationLabel = 'فرم استعلام قیمت';

    protected static ?string $title = 'تنظیمات فرم استعلام قیمت';

    protected static ?string $slug = 'settings/quote-form';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['fields' => QuoteForm::config()]);
    }

    public function form(Schema $schema): Schema
    {
        $rows = [];

        foreach (QuoteForm::OPTIONAL_FIELDS as $key => [$label]) {
            $rows[] = Grid::make(['default' => 1, 'sm' => 3])->schema([
                Toggle::make("fields.{$key}.enabled")
                    ->label($label)
                    ->live()
                    ->columnSpan(['default' => 1, 'sm' => 2]),
                Toggle::make("fields.{$key}.required")
                    ->label('الزامی')
                    ->disabled(fn (Get $get): bool => ! $get("fields.{$key}.enabled")),
            ]);
        }

        return $schema
            ->components([
                Section::make('فیلدهای اختیاری فرم')
                    ->description('نام، شماره تماس، روش حمل، مبدأ و مقصد همیشه در فرم هستند و الزامی‌اند. بقیه را این‌جا روشن یا خاموش کنید.')
                    ->schema($rows),
            ])
            ->statePath('data');
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
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $fields = $this->form->getState()['fields'] ?? [];
        $clean = [];

        foreach (array_keys(QuoteForm::OPTIONAL_FIELDS) as $key) {
            $enabled = (bool) ($fields[$key]['enabled'] ?? false);
            $clean[$key] = [
                'enabled' => $enabled,
                'required' => $enabled && (bool) ($fields[$key]['required'] ?? false),
            ];
        }

        Setting::set('quote_form', $clean);

        Notification::make()->success()->title('تنظیمات فرم ذخیره شد')->send();
    }
}
