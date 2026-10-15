<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditQuoteRequest extends EditRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('دانلود پیوست')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => filled($this->record->attachment_path)
                    && Storage::disk('local')->exists($this->record->attachment_path))
                ->action(fn () => Storage::disk('local')->download(
                    $this->record->attachment_path,
                    $this->record->attachment_name,
                )),
            Action::make('call')
                ->label('تماس')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->url(fn (): string => 'tel:'.$this->record->phone),
            DeleteAction::make(),
        ];
    }
}
