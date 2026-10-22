<?php

namespace App\Support;

use App\Models\ChatConversation;
use App\Models\User;
use App\Notifications\NewChatMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Emails the customer team about visitor chat messages, but only when nobody
 * has the panel open (otherwise the panel's sound + notification is enough).
 * Follow-up messages in the same conversation send at most one email per 30 minutes.
 */
class ChatAlerts
{
    private const COOLDOWN_MINUTES = 30;

    public static function visitorWrote(ChatConversation $conversation, string $body, bool $isNew): void
    {
        if (ChatPresence::online()) {
            return;
        }

        // Cache::add only succeeds once per window, so a chatty visitor doesn't flood the inbox.
        $key = "chat:email-alert:{$conversation->id}";
        if (! Cache::add($key, true, now()->addMinutes(self::COOLDOWN_MINUTES))) {
            return;
        }

        try {
            $team = User::whereIn('role', Roles::CUSTOMER_TEAM)->get();
            Notification::send($team, new NewChatMessage($conversation, $body, $isNew));
        } catch (\Throwable $e) {
            // An email problem must never break the visitor's chat.
            Log::warning('Chat email alert failed: '.$e->getMessage());
        }
    }
}
