<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactMessage extends EditRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('call')
                ->label('تماس')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->url(fn (): string => 'tel:'.$this->record->phone),
            DeleteAction::make(),
        ];
    }
}
