<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Filament\Resources\Shipments\ShipmentResource;
use App\Models\Shipment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditQuoteRequest extends EditRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Customer accepted the price → open a shipment with the quote's details.
            Action::make('create_shipment')
                ->label('ایجاد محموله')
                ->icon('heroicon-o-truck')
                ->visible(fn (): bool => ! $this->existingShipment())
                ->requiresConfirmation()
                ->modalHeading('ایجاد محموله از این استعلام')
                ->modalDescription('اطلاعات مسیر و مشتری به محموله‌ی جدید منتقل می‌شود، وضعیت استعلام «تبدیل به سفارش» می‌شود و کد رهگیری ساخته می‌شود.')
                ->modalSubmitActionLabel('ایجاد محموله')
                ->action(function () {
                    $quote = $this->record;

                    $shipment = Shipment::create([
                        'quote_request_id' => $quote->id,
                        'customer_name' => $quote->company ? "{$quote->name} ({$quote->company})" : $quote->name,
                        'customer_phone' => $quote->phone,
                        'transport_mode' => $quote->transport_mode === 'unsure' ? 'road' : $quote->transport_mode,
                        'origin' => $quote->origin,
                        'destination' => $quote->destination,
                        'cargo_description' => $quote->cargo_type,
                    ]);
                    $shipment->startTracking();

                    $quote->update(['status' => 'won']);

                    Notification::make()
                        ->title("محموله با کد {$shipment->tracking_code} ساخته شد")
                        ->success()
                        ->send();

                    $this->redirect(ShipmentResource::getUrl('edit', ['record' => $shipment]));
                }),
            Action::make('open_shipment')
                ->label('مشاهده‌ی محموله')
                ->icon('heroicon-o-truck')
                ->color('gray')
                ->visible(fn (): bool => (bool) $this->existingShipment())
                ->url(fn (): string => ShipmentResource::getUrl('edit', ['record' => $this->existingShipment()])),
            Action::make('download')
                ->label('دانلود پیوست')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
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

    private function existingShipment(): ?Shipment
    {
        return Shipment::where('quote_request_id', $this->record->id)->first();
    }
}
