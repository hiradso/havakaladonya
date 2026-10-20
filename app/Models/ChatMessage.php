<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = ['sender', 'user_id', 'body'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** What the visitor's browser receives (no internal ids of team members). */
    public function toPublic(): array
    {
        return [
            'id' => $this->id,
            'from' => $this->sender,
            'body' => $this->body,
            'at' => $this->created_at->toIso8601String(),
        ];
    }
}
