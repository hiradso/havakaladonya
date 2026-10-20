<?php

namespace App\Filament\Pages;

use App\Models\ChatConversation;
use App\Support\ChatPresence;
use App\Support\Roles;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Live chat inbox for the team. Refreshes every few seconds (wire:poll),
 * so new visitor messages appear without reloading.
 */
class ChatInbox extends Page
{
    protected string $view = 'filament.pages.chat-inbox';

    protected static ?string $slug = 'chat';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string | UnitEnum | null $navigationGroup = 'فروش';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'گفتگوی آنلاین';

    protected static ?string $title = 'گفتگوی آنلاین';

    protected Width | string | null $maxContentWidth = Width::Full;

    public ?int $selectedId = null;

    /** open | closed */
    public string $filter = 'open';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(...Roles::CUSTOMER_TEAM) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ChatConversation::withUnread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function mount(): void
    {
        $this->selectedId = ChatConversation::where('status', 'open')->latest('last_message_at')->value('id');
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['open', 'closed'], true) ? $filter : 'open';
    }

    public function send(string $body): void
    {
        $body = trim($body);
        $conversation = $this->selected();

        if ($body === '' || ! $conversation || mb_strlen($body) > 2000) {
            return;
        }

        $conversation->addMessage('agent', $body, auth()->id());

        if (! $conversation->assigned_to) {
            $conversation->forceFill(['assigned_to' => auth()->id()])->save();
        }
    }

    public function close(): void
    {
        $this->selected()?->forceFill(['status' => 'closed'])->save();
    }

    public function reopen(): void
    {
        $this->selected()?->forceFill(['status' => 'open'])->save();
    }

    public function selected(): ?ChatConversation
    {
        return $this->selectedId ? ChatConversation::find($this->selectedId) : null;
    }

    protected function getViewData(): array
    {
        ChatPresence::ping();

        $conversations = ChatConversation::query()
            ->where('status', $this->filter)
            ->latest('last_message_at')
            ->limit(100)
            ->get();

        $selected = $this->selected();
        $messages = new Collection;

        if ($selected) {
            // Newest 300, shown oldest → newest. (reorder() drops the relation's default ascending order.)
            $messages = $selected->messages()->reorder()->with('author:id,name')->latest('id')->limit(300)->get()->reverse()->values();
            // Opening a conversation marks it as read for the whole team.
            $lastId = (int) $messages->last()?->id;
            if ($lastId > $selected->agent_read_id) {
                $selected->forceFill(['agent_read_id' => $lastId])->saveQuietly();
            }
        }

        $unread = ChatConversation::query()
            ->whereIn('id', $conversations->pluck('id'))
            ->get(['id', 'agent_read_id'])
            ->mapWithKeys(fn (ChatConversation $c) => [$c->id => $c->unreadForAgents()]);

        return [
            'conversations' => $conversations,
            'selected' => $selected,
            'messages' => $messages,
            'unread' => $unread,
            'openCount' => ChatConversation::where('status', 'open')->count(),
        ];
    }
}
