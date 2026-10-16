<?php

namespace App\Notifications;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewContactMessage extends Notification
{
    use Queueable;

    public function __construct(public ContactMessage $contact) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->contact;

        return (new MailMessage)
            ->subject("پیام جدید از سایت: {$m->name}")
            ->greeting('پیام جدید از فرم تماس')
            ->line("نام: {$m->name}")
            ->line("تلفن: {$m->phone}")
            ->line('پیام: '.Str::limit($m->message, 300))
            ->action('مشاهده در پنل', ContactMessageResource::getUrl('edit', ['record' => $m], panel: 'admin'));
    }
}
