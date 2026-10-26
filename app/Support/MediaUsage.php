<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Post;
use App\Models\Setting;

/**
 * Finds where a media-library picture is used, so the panel can refuse to delete it.
 *
 * Content settings refer to pictures by id under keys ending in "_media" (e.g. "hero_media": 12)
 * or in lists named "*_media_ids". Articles may contain the picture's URL in their text.
 */
class MediaUsage
{
    /** Human labels for content settings keys, shown in the warning. */
    public const SETTING_LABELS = [
        'content_home' => 'صفحه اصلی',
        'content_about' => 'درباره ما',
        'content_contact' => 'تماس با ما',
        'content_footer' => 'فوتر',
    ];

    /** @return list<string> */
    public static function for(Media $media): array
    {
        $places = [];

        foreach (Setting::query()->where('key', 'like', 'content_%')->get(['key', 'value']) as $setting) {
            if (self::containsId($setting->value, $media->id)) {
                $places[] = self::SETTING_LABELS[$setting->key] ?? $setting->key;
            }
        }

        $paths = $media->allPaths();
        $posts = Post::query()
            ->where(function ($q) use ($paths) {
                foreach ($paths as $path) {
                    $q->orWhere('cover_path', $path)->orWhere('body', 'like', '%'.$path.'%');
                }
            })
            ->limit(5)
            ->pluck('title');

        foreach ($posts as $title) {
            $places[] = 'مقاله: '.$title;
        }

        return $places;
    }

    private static function containsId(mixed $value, int $id): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            $key = (string) $key;
            if (str_ends_with($key, '_media') && (int) $item === $id) {
                return true;
            }
            if (str_ends_with($key, '_media_ids') && is_array($item) && in_array($id, array_map('intval', $item), true)) {
                return true;
            }
            if (is_array($item) && self::containsId($item, $id)) {
                return true;
            }
        }

        return false;
    }
}
