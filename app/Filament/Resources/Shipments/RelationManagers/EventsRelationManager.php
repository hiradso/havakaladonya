<?php

namespace App\Filament\Resources\Shipments\RelationManagers;

use App\Models\TrackingStage;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'مراحل رهگیری';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tracking_stage_id')
                ->label('مرحله')
                ->options(fn () => TrackingStage::where('is_active', true)->ordered()->pluck('name_fa', 'id'))
                ->default(fn () => $this->suggestedNextStageId())
                ->required()
                ->native(false),
            DateTimePicker::make('happened_at')
                ->label('زمان')
                ->default(now())
                ->required()
                ->seconds(false)
                ->native(false),
            TextInput::make('location')
                ->label('مکان')
                ->placeholder('مثلاً مرز بازرگان')
                ->maxLength(255),
            Textarea::make('note')
                ->label('توضیح برای مشتری')
                ->helperText('این متن در صفحه‌ی رهگیری به مشتری نشان داده می‌شود.')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->defaultSort('happened_at', 'desc')
            ->modelLabel('مرحله')
            ->pluralModelLabel('مراحل')
            ->columns([
                TextColumn::make('stage.name_fa')->label('مرحله')->badge()
                    ->color(fn ($record) => $record->stage?->is_final ? 'success' : 'info'),
                TextColumn::make('happened_at')->label('زمان')->jalali(withTime: true)->sortable(),
                TextColumn::make('location')->label('مکان')->placeholder('—'),
                TextColumn::make('note')->label('توضیح')->limit(60)->wrap()->placeholder('—'),
                TextColumn::make('author.name')->label('ثبت توسط')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('ثبت مرحله‌ی جدید'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('هنوز مرحله‌ای ثبت نشده');
    }

    /** Pre-selects the stage after the current one, so most updates are two clicks. */
    protected function suggestedNextStageId(): ?int
    {
        $stages = TrackingStage::where('is_active', true)->ordered()->pluck('id')->values();
        $current = $this->getOwnerRecord()->current_stage_id;
        $index = $current ? $stages->search($current) : false;

        if ($index === false) {
            return $stages->first();
        }

        return $stages->get($index + 1) ?? $stages->get($index);
    }
}
