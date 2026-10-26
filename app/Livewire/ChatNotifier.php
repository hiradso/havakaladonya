<?php

namespace App\Livewire;

use App\Filament\Pages\ChatInbox;
use App\Models\ChatMessage;
use App\Support\ChatPresence;
use App\Support\Roles;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
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
        if (! auth()->user()?->hasRole(...Roles::CUSTOMER_TEAM)) {
            return;
        }

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
            ->body(Str::limit($latest->body, 120))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->info()
            ->actions([
                Action::make('open')->label('پاسخ')->url(ChatInbox::getUrl()),
            ])
            ->send();

        $this->dispatch('hkd-chat-new', name: $name, body: Str::limit($latest->body, 120));
    }

    public function render(): string
    {
        // The handler lives in a method: Alpine event attributes must be expressions, and a bare
        // `try { ... }` statement there is a syntax error that silently disabled the sound.
        return <<<'BLADE'
            <div
                wire:poll.8s="check"
                x-data="{
                    alert(detail) {
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
                        if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                            new Notification('پیام جدید از ' + detail.name, { body: detail.body, icon: '/images/brand/favicon-32.png' });
                        }
                    },
                    init() {
                        if ('Notification' in window && Notification.permission === 'default') {
                            document.addEventListener('click', () => Notification.requestPermission(), { once: true });
                        }
                    },
                }"
                x-on:hkd-chat-new.window="alert($event.detail)"
            ></div>
        BLADE;
    }
}
