<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Simple key/value site settings (brand color, contact info, ...).
 * All values are cached together; the cache is cleared on every write.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public const CACHE_KEY = 'site-settings';

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $all = Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            // Table does not exist yet (before `php artisan migrate`).
            return $default;
        }

        return $all[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::CACHE_KEY);
    }
}
