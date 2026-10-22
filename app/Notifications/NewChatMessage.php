<?php

namespace App\Notifications;

use App\Filament\Pages\ChatInbox;
use App\Models\ChatConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Email to the customer team when a website visitor writes in the live chat
 * while nobody has the admin panel open.
 */
class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ChatConversation $conversation, public string $body, public bool $isNew) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $c = $this->conversation;

        return (new MailMessage)
            ->subject(($this->isNew ? 'گفتگوی جدید در سایت: ' : 'پیام تازه در گفتگو: ').$c->name)
            ->greeting($this->isNew ? 'یک بازدیدکننده گفتگوی تازه‌ای شروع کرده است' : 'بازدیدکننده پیام تازه‌ای فرستاده است')
            ->line("نام: {$c->name}")
            ->line("تلفن: {$c->phone}")
            ->line('پیام: '.Str::limit($this->body, 300))
            ->line('در این لحظه هیچ‌کس از تیم در پنل آنلاین نبود.')
            ->action('پاسخ در پنل', ChatInbox::getUrl(panel: 'admin'));
    }
}
