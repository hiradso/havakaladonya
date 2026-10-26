<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A service page (air freight, customs clearance, ...), edited in the panel: Content → Services.
 * Texts for both languages live in the "content" JSON column.
 */
class Service extends Model
{
    /** Icons the website knows (resources/js/components/site/service-icon.tsx). */
    public const ICONS = [
        'plane' => 'هواپیما',
        'ship' => 'کشتی',
        'truck' => 'کامیون',
        'train' => 'قطار',
        'file-check' => 'مدارک و گمرک',
        'globe' => 'کره‌ی زمین',
        'luggage' => 'چمدان',
        'lightbulb' => 'لامپ (مشاوره)',
        'briefcase' => 'کیف (بازرگانی)',
        'package' => 'بسته',
        'warehouse' => 'انبار',
        'shield' => 'سپر (بیمه)',
        'container' => 'کانتینر',
        'handshake' => 'دست دادن',
    ];

    public const QUOTE_MODES = [
        'air' => 'هوایی',
        'sea' => 'دریایی',
        'road' => 'جاده‌ای',
        'rail' => 'ریلی',
    ];

    protected $fillable = ['slug', 'sort', 'is_active', 'icon', 'quote_mode', 'media_id', 'photo_default', 'content'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'content' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            $service->slug = Post::makeSlug((string) ($service->slug ?: ($service->content['en']['title'] ?? $service->content['fa']['title'] ?? 'service')));
        });
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function text(string $locale, string $key, mixed $default = ''): mixed
    {
        $value = $this->content[$locale][$key] ?? null;
        if (blank($value) && $locale === 'en') {
            $value = $this->content['fa'][$key] ?? null; // missing English → Persian rather than empty
        }

        return blank($value) ? $default : $value;
    }

    /** Short version for lists (home page, services page, "other services"). */
    public function toCard(string $locale): array
    {
        return [
            'slug' => $this->slug,
            'icon' => $this->icon,
            'title' => (string) $this->text($locale, 'title'),
            'short' => (string) $this->text($locale, 'short'),
        ];
    }

    /** Everything the service page needs. */
    public function toPage(string $locale): array
    {
        $photo = null;
        if ($this->media) {
            $photo = $this->media->toImage($locale);
        } elseif ($this->photo_default) {
            $photo = ['src' => "/images/photos/{$this->photo_default}.webp", 'srcset' => '', 'width' => 800, 'height' => 450, 'alt' => ''];
        }

        $clean = fn (mixed $list) => array_values(array_filter((array) $list, fn ($v) => filled($v)));
        $chips = (array) $this->text($locale, 'chips', []);

        return $this->toCard($locale) + [
            'lead' => (string) $this->text($locale, 'lead'),
            'body' => $clean($this->text($locale, 'body', [])),
            'features' => $clean($this->text($locale, 'features', [])),
            'groups' => array_values(array_filter(array_map(fn ($g) => [
                'title' => (string) ($g['title'] ?? ''),
                'items' => $clean($g['items'] ?? []),
            ], (array) $this->text($locale, 'groups', [])), fn ($g) => $g['items'] !== [])),
            'chips' => filled($chips['items'] ?? null) ? ['title' => (string) ($chips['title'] ?? ''), 'items' => $clean($chips['items'])] : null,
            'quoteMode' => $this->quote_mode,
            'metaTitle' => (string) $this->text($locale, 'metaTitle'),
            'metaDescription' => (string) $this->text($locale, 'metaDescription'),
            'photo' => $photo,
        ];
    }
}
