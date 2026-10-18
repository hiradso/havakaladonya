<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Digits;
use Illuminate\Support\Str;

class Post extends Model
{
    public const STATUSES = [
        'draft' => 'پیش‌نویس',
        'published' => 'منتشر شده',
    ];

    public const LOCALES = [
        'fa' => 'فارسی',
        'en' => 'English',
    ];

    protected $fillable = [
        'locale', 'title', 'slug', 'excerpt', 'body', 'cover_path', 'status', 'published_at',
        'meta_title', 'meta_description', 'author_id', 'legacy_path', 'wordpress_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if (blank($post->slug)) {
                $post->slug = $post->title;
            }
            $post->slug = static::uniqueSlug(static::makeSlug($post->slug), $post->locale, $post->id);

            // Publishing without a date means "now".
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    /**
     * URL-friendly slug that keeps Persian letters (good for Persian search results):
     * "حمل و نقل ریلی!" → "حمل-و-نقل-ریلی".
     */
    public static function makeSlug(string $text): string
    {
        $text = str_replace(["\u{200C}", "\u{200F}", "\u{200E}"], '-', $text); // zero-width chars
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower(trim($text))) ?? '';

        return Str::limit(trim($slug, '-'), 120, '') ?: 'post';
    }

    public static function uniqueSlug(string $slug, string $locale, ?int $ignoreId = null): string
    {
        $candidate = $slug;
        $i = 2;

        while (static::where('locale', $locale)->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Address on the website, e.g. /blog/… or /en/blog/… */
    public function url(): string
    {
        return ($this->locale === 'en' ? '/en' : '').'/blog/'.rawurlencode($this->slug);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? '/storage/'.ltrim($this->cover_path, '/') : null;
    }

    /** Rough reading time: about 200 words per minute. */
    public function readingMinutes(): int
    {
        $words = count(preg_split('/\s+/u', trim(strip_tags((string) $this->body))) ?: []);

        return max(1, (int) round($words / 200));
    }

    /** What list pages need about a post. Persian articles get Persian digits. */
    public function toCard(): array
    {
        $excerpt = $this->excerpt ?: Str::limit(trim(html_entity_decode(strip_tags((string) $this->body))), 180);

        return [
            'slug' => $this->slug,
            'url' => $this->url(),
            'title' => $this->localDigits($this->title),
            'excerpt' => $this->localDigits($excerpt),
            'cover' => $this->coverUrl(),
            'date' => $this->published_at?->toIso8601String(),
            'minutes' => $this->readingMinutes(),
        ];
    }

    public function localDigits(string $text): string
    {
        return $this->locale === 'fa' ? Digits::toPersian($text) : $text;
    }

    /** Article HTML for the website: sanitized, with Persian digits in the text of Persian articles. */
    public function renderedBody(): string
    {
        $html = Str::sanitizeHtml((string) $this->body);

        return $this->locale === 'fa' ? Digits::toPersianHtml($html) : $html;
    }
}
