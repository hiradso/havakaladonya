<?php

namespace App\Models;

use App\Support\MediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * One picture in the media library (admin → Content → Media).
 * Files are already optimized (WebP, a few sizes) by App\Support\ImageOptimizer when uploaded.
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'title', 'alt_fa', 'alt_en', 'path', 'variants', 'width', 'height', 'size', 'mime', 'original_name', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Removing a record also removes its files.
        static::deleted(function (Media $media): void {
            Storage::disk('public')->delete($media->allPaths());
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return list<string> */
    public function allPaths(): array
    {
        return array_values(array_unique([
            $this->path,
            ...array_column($this->variants ?? [], 'path'),
        ]));
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /** Smallest copy, for thumbnails in the panel. */
    public function thumbUrl(): string
    {
        $first = $this->variants[0]['path'] ?? $this->path;

        return Storage::disk('public')->url($first);
    }

    public function srcset(): string
    {
        $disk = Storage::disk('public');
        $items = collect($this->variants ?? [])
            ->map(fn (array $v) => $disk->url($v['path']).' '.$v['w'].'w')
            ->push($this->url().' '.$this->width.'w')
            ->unique();

        return $items->implode(', ');
    }

    public function alt(string $locale = 'fa'): string
    {
        return (string) ($locale === 'en' ? ($this->alt_en ?: $this->alt_fa) : ($this->alt_fa ?: $this->alt_en));
    }

    /**
     * Everything the website needs to show this picture responsively.
     *
     * @return array{id: int, src: string, srcset: string, width: int, height: int, alt: string}
     */
    public function toImage(string $locale = 'fa'): array
    {
        return [
            'id' => $this->id,
            'src' => $this->url(),
            'srcset' => $this->srcset(),
            'width' => $this->width,
            'height' => $this->height,
            'alt' => $this->alt($locale),
        ];
    }

    /** Places on the site that use this picture (so it is not deleted by accident). @return list<string> */
    public function usages(): array
    {
        return MediaUsage::for($this);
    }

    public function humanSize(): string
    {
        $kb = $this->size / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1).' MB' : number_format($kb).' KB';
    }
}
