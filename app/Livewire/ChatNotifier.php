<?php

namespace App\Livewire;

use App\Filament\Pages\ChatInbox;
use App\Models\ChatMessage;
use App\Support\ChatPresence;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Livewire\Component;

/**
 * Sits on every admin page: marks the team as "online" for the website chat and
 * plays a sound + shows a notification when a visitor sends a new message.
 */
class ChatNotifier extends Component
{
    public int $lastId = 0;

    public function mount(): void
    {
        $this->lastId = (int) ChatMessage::where('sender', 'visitor')->max('id');
    }

    public function check(): void
    {
        ChatPresence::ping();

        $new = ChatMessage::with('conversation:id,name')
            ->where('sender', 'visitor')
            ->where('id', '>', $this->lastId)
            ->orderBy('id')
            ->get();

        if ($new->isEmpty()) {
            return;
        }

        $this->lastId = (int) $new->last()->id;
        $latest = $new->last();
        $name = $latest->conversation?->name ?? 'بازدیدکننده';

        Notification::make()
            ->title("پیام جدید از {$name}")
            ->body(\Illuminate\Support\Str::limit($latest->body, 120))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->info()
            ->actions([
                Action::make('open')->label('پاسخ')->url(ChatInbox::getUrl()),
            ])
            ->send();

        $this->dispatch('hkd-chat-new', name: $name, body: \Illuminate\Support\Str::limit($latest->body, 120));
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div wire:poll.8s="check" x-data x-on:hkd-chat-new.window="
                // Short two-tone beep (no audio file needed)
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    [880, 660].forEach((f, i) => {
                        const o = ctx.createOscillator(); const g = ctx.createGain();
                        o.frequency.value = f; o.connect(g); g.connect(ctx.destination);
                        g.gain.setValueAtTime(0.15, ctx.currentTime + i * 0.18);
                        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.18 + 0.16);
                        o.start(ctx.currentTime + i * 0.18); o.stop(ctx.currentTime + i * 0.18 + 0.17);
                    });
                } catch (e) {}
                // Browser notification when the panel tab is in the background
                if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                    new Notification('پیام جدید از ' + $event.detail.name, { body: $event.detail.body, icon: '/images/brand/favicon-32.png' });
                }
            " x-init="if ('Notification' in window && Notification.permission === 'default') { document.addEventListener('click', () => Notification.requestPermission(), { once: true }); }"></div>
        BLADE;
    }
}
