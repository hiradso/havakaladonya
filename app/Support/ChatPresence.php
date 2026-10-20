<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * "Is anyone from the team online?" — the admin panel pings every few seconds while open;
 * the team counts as online for 60 seconds after the last ping.
 */
class ChatPresence
{
    private const KEY = 'chat:agents-online-until';

    public static function ping(): void
    {
        Cache::put(self::KEY, now()->addSeconds(60)->getTimestamp(), 120);
    }

    public static function online(): bool
    {
        return (int) Cache::get(self::KEY, 0) > now()->getTimestamp();
    }
}
