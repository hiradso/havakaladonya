<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    protected $fillable = ['name', 'phone', 'locale', 'status', 'assigned_to', 'page_url', 'ip_address'];

    protected $hidden = ['token', 'ip_address'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'visitor_seen_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ChatConversation $c): void {
            $c->token ??= Str::random(48);
            $c->status ??= 'open';
        });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Visitor messages the team has not seen yet. */
    public function unreadForAgents(): int
    {
        return $this->messages()->where('sender', 'visitor')->where('id', '>', $this->agent_read_id)->count();
    }

    /** Conversations with at least one visitor message nobody on the team has seen. */
    public function scopeWithUnread(Builder $query): Builder
    {
        return $query->whereExists(fn ($q) => $q->from('chat_messages')
            ->whereColumn('chat_messages.chat_conversation_id', 'chat_conversations.id')
            ->where('chat_messages.sender', 'visitor')
            ->whereColumn('chat_messages.id', '>', 'chat_conversations.agent_read_id'));
    }

    public function addMessage(string $sender, string $body, ?int $userId = null): ChatMessage
    {
        $message = $this->messages()->create(['sender' => $sender, 'body' => $body, 'user_id' => $userId]);

        $this->forceFill([
            'last_message_at' => $message->created_at,
            // Writing a message means you have read everything before it.
            $sender === 'agent' ? 'agent_read_id' : 'visitor_read_id' => $message->id,
            // A new visitor message reopens a closed conversation.
            'status' => $sender === 'visitor' ? 'open' : $this->status,
        ])->save();

        return $message;
    }
}
