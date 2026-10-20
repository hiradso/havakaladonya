<?php

namespace App\Notifications;

use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewQuoteRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public QuoteRequest $quote) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $q = $this->quote;

        return (new MailMessage)
            ->subject("استعلام قیمت جدید {$q->reference}")
            ->greeting('استعلام قیمت جدید')
            ->line("نام: {$q->name}")
            ->line("تلفن: {$q->phone}")
            ->line('روش حمل: '.(QuoteRequest::TRANSPORT_MODES[$q->transport_mode] ?? $q->transport_mode))
            ->line("مسیر: {$q->origin} ← {$q->destination}")
            ->action('مشاهده در پنل', QuoteRequestResource::getUrl('edit', ['record' => $q], panel: 'admin'));
    }
}
