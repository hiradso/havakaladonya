<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Forms\MediaPicker;
use App\Filament\Resources\Media\MediaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;

class ManageMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('آپلود تصویر')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => MediaResource::canCreate())
                ->modalHeading('آپلود تصویر')
                ->modalDescription('می‌توانید چند عکس را با هم انتخاب کنید. عکس‌ها به WebP تبدیل و در چند اندازه ذخیره می‌شوند تا سایت سریع بماند.')
                ->modalSubmitActionLabel('آپلود')
                ->schema(MediaPicker::uploadSchema(multiple: true))
                ->action(fn (array $data) => MediaPicker::importUploads($data)),
        ];
    }
}
