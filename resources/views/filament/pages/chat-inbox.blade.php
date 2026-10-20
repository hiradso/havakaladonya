<x-filament-panels::page>
    <style>
        .hkd-chat { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; min-height: 70vh; }
        @media (min-width: 1024px) { .hkd-chat { grid-template-columns: 20rem minmax(0, 1fr); } }
        .hkd-card { background: white; border: 1px solid var(--gray-200); border-radius: 0.75rem; overflow: hidden; }
        .dark .hkd-card { background: var(--gray-900); border-color: rgb(255 255 255 / 0.1); }
        .hkd-tabs { display: flex; gap: 0.25rem; padding: 0.5rem; border-bottom: 1px solid var(--gray-200); }
        .dark .hkd-tabs, .dark .hkd-head, .dark .hkd-compose { border-color: rgb(255 255 255 / 0.1); }
        .hkd-tab { flex: 1; padding: 0.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--gray-500); }
        .hkd-tab[aria-pressed="true"] { background: var(--gray-100); color: var(--gray-950); }
        .dark .hkd-tab[aria-pressed="true"] { background: rgb(255 255 255 / 0.08); color: white; }
        .hkd-list { max-height: 70vh; overflow-y: auto; }
        .hkd-item { display: flex; width: 100%; gap: 0.75rem; align-items: flex-start; padding: 0.75rem 1rem; text-align: start; border-bottom: 1px solid var(--gray-100); }
        .dark .hkd-item { border-color: rgb(255 255 255 / 0.05); }
        .hkd-item:hover { background: var(--gray-50); }
        .dark .hkd-item:hover { background: rgb(255 255 255 / 0.04); }
        .hkd-item[aria-current="true"] { background: color-mix(in oklab, var(--primary-500) 12%, transparent); }
        .hkd-badge { min-width: 1.4rem; height: 1.4rem; padding: 0 0.35rem; border-radius: 999px; background: var(--danger-600, #dc2626); color: white; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
        .hkd-muted { color: var(--gray-500); font-size: 0.8rem; }
        .hkd-thread { display: flex; flex-direction: column; height: 70vh; }
        .hkd-head { display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200); }
        .hkd-msgs { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .hkd-msg { max-width: 75%; padding: 0.6rem 0.85rem; border-radius: 1rem; line-height: 1.9; overflow-wrap: anywhere; }
        .hkd-body { white-space: pre-wrap; }
        .hkd-msg.visitor { align-self: flex-start; background: var(--gray-100); border-start-start-radius: 0.25rem; }
        .dark .hkd-msg.visitor { background: rgb(255 255 255 / 0.08); }
        .hkd-msg.agent { align-self: flex-end; background: var(--primary-500); color: var(--gray-950); border-start-end-radius: 0.25rem; }
        .hkd-time { display: block; font-size: 0.7rem; opacity: 0.7; margin-top: 0.15rem; }
        .hkd-compose { display: flex; gap: 0.5rem; padding: 0.75rem; border-top: 1px solid var(--gray-200); align-items: flex-end; }
        .hkd-compose textarea { flex: 1; resize: none; border-radius: 0.6rem; border: 1px solid var(--gray-300); padding: 0.6rem 0.75rem; background: transparent; min-height: 2.75rem; max-height: 9rem; }
        .dark .hkd-compose textarea { border-color: rgb(255 255 255 / 0.15); }
        .hkd-empty { display: flex; flex: 1; align-items: center; justify-content: center; color: var(--gray-500); padding: 2rem; text-align: center; }
    </style>

    <div class="hkd-chat" wire:poll.4s>
        {{-- Conversations --}}
        <div class="hkd-card">
            <div class="hkd-tabs">
                <button type="button" class="hkd-tab" aria-pressed="{{ $filter === 'open' ? 'true' : 'false' }}" wire:click="setFilter('open')">
                    باز ({{ $openCount }})
                </button>
                <button type="button" class="hkd-tab" aria-pressed="{{ $filter === 'closed' ? 'true' : 'false' }}" wire:click="setFilter('closed')">
                    بسته‌شده
                </button>
            </div>
            <div class="hkd-list">
                @forelse ($conversations as $c)
                    <button type="button" class="hkd-item" wire:key="c-{{ $c->id }}" wire:click="select({{ $c->id }})" aria-current="{{ $selected?->id === $c->id ? 'true' : 'false' }}">
                        <div style="flex: 1; min-width: 0">
                            <div style="font-weight: 700">{{ $c->name }}</div>
                            <div class="hkd-muted" dir="ltr" style="text-align: start">{{ $c->phone }}</div>
                            <div class="hkd-muted">{{ $c->last_message_at?->diffForHumans() }}</div>
                        </div>
                        @if (($unread[$c->id] ?? 0) > 0)
                            <span class="hkd-badge">{{ $unread[$c->id] }}</span>
                        @endif
                    </button>
                @empty
                    <div class="hkd-empty">گفتگویی نیست.</div>
                @endforelse
            </div>
        </div>

        {{-- Selected conversation --}}
        <div class="hkd-card hkd-thread">
            @if ($selected)
                <div class="hkd-head">
                    <div>
                        <div style="font-weight: 800; font-size: 1.05rem">{{ $selected->name }}</div>
                        <div class="hkd-muted">
                            <a href="tel:{{ $selected->phone }}" dir="ltr" style="text-decoration: underline">{{ $selected->phone }}</a>
                            · {{ $selected->locale === 'en' ? 'English' : 'فارسی' }}
                            @if ($selected->visitor_seen_at)
                                · آخرین حضور در سایت {{ $selected->visitor_seen_at->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem">
                        @if ($selected->status === 'open')
                            <x-filament::button color="gray" size="sm" wire:click="close" icon="heroicon-o-check-circle">بستن گفتگو</x-filament::button>
                        @else
                            <x-filament::button color="gray" size="sm" wire:click="reopen" icon="heroicon-o-arrow-path">باز کردن دوباره</x-filament::button>
                        @endif
                    </div>
                </div>

                <div class="hkd-msgs" id="hkd-msgs" x-data x-init="$el.scrollTop = $el.scrollHeight">
                    @foreach ($messages as $m)
                        <div class="hkd-msg {{ $m->sender }}" wire:key="m-{{ $m->id }}">
                            <div class="hkd-body" dir="auto">{{ $m->body }}</div>
                            <span class="hkd-time">
                                {{ $m->sender === 'agent' ? ($m->author?->name ?? 'کارشناس') . ' · ' : '' }}{{ $m->created_at->timezone('Asia/Tehran')->format('H:i') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- wire:ignore keeps what you are typing while the list refreshes --}}
                <div class="hkd-compose" wire:ignore x-data="{ text: '', sending: false }">
                    <textarea
                        x-model="text"
                        rows="1"
                        placeholder="پاسخ خود را بنویسید… (Enter برای ارسال، Shift+Enter خط جدید)"
                        x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $refs.send.click(); }"
                    ></textarea>
                    <x-filament::button
                        x-ref="send"
                        icon="heroicon-o-paper-airplane"
                        x-bind:disabled="sending || ! text.trim()"
                        x-on:click="sending = true; $wire.send(text).then(() => { text = ''; sending = false; })"
                    >
                        ارسال
                    </x-filament::button>
                </div>
            @else
                <div class="hkd-empty">یک گفتگو را انتخاب کنید.</div>
            @endif
        </div>
    </div>

    @script
        <script>
            // Keep the newest message in view after each refresh, unless the agent scrolled up to read.
            let stick = true;
            document.addEventListener('scroll', (e) => {
                if (e.target?.id === 'hkd-msgs') {
                    stick = e.target.scrollHeight - e.target.scrollTop - e.target.clientHeight < 80;
                }
            }, true);
            Livewire.hook('morph.updated', () => {
                const box = document.getElementById('hkd-msgs');
                if (box && stick) box.scrollTop = box.scrollHeight;
            });
        </script>
    @endscript
</x-filament-panels::page>
