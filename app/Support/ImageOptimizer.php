<?php

namespace App\Support;

use App\Models\Media;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Turns an uploaded photo into light, web-ready files for the media library (PHP GD only, no extra packages):
 * - fixes the rotation of phone photos (EXIF),
 * - makes WebP copies at a few widths for phones, tablets and desktops,
 * - never enlarges a small picture.
 */
class ImageOptimizer
{
    /** Widths of the copies; the largest one is the "main" file. */
    public const WIDTHS = [480, 960, 1600, 2400];

    public const QUALITY = 82;

    /** Uploads bigger than this many megapixels are refused (protects the server's memory). */
    public const MAX_MEGAPIXELS = 50;

    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /**
     * Optimizes a file (absolute path on the server) and saves it as a Media record.
     *
     * @param  array{title?: ?string, alt_fa?: ?string, alt_en?: ?string, uploaded_by?: ?int}  $attributes
     */
    public static function import(string $sourcePath, ?string $originalName = null, array $attributes = []): Media
    {
        $info = @getimagesize($sourcePath);
        if (! $info || ! in_array($info['mime'], self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('فقط عکس‌های JPG، PNG، WebP یا GIF قابل آپلود هستند.');
        }
        if (self::MAX_MEGAPIXELS * 1_000_000 < $info[0] * $info[1]) {
            throw new RuntimeException('ابعاد این عکس خیلی بزرگ است. لطفاً عکسی با ابعاد کمتر آپلود کنید.');
        }

        // A 24-megapixel photo needs ~100 MB while being resized; the default 128 MB is too tight.
        if (self::bytes((string) ini_get('memory_limit')) < 512 * 1024 * 1024 && ini_get('memory_limit') !== '-1') {
            @ini_set('memory_limit', '512M');
        }

        $image = self::load($sourcePath, $info['mime']);
        $image = self::fixOrientation($image, $sourcePath, $info['mime']);
        $width = imagesx($image);
        $height = imagesy($image);

        $disk = Storage::disk('public');
        $base = 'media/'.now()->format('Y/m').'/'.self::baseName($originalName);
        $useWebp = function_exists('imagewebp');
        $ext = $useWebp ? 'webp' : 'jpg';

        // Target widths smaller than the picture, plus the picture's own width (capped at the largest).
        $targets = array_values(array_filter(self::WIDTHS, fn (int $w) => $w < $width));
        $targets[] = min($width, max(self::WIDTHS));
        $targets = array_values(array_unique($targets));
        sort($targets);

        $files = [];
        foreach ($targets as $w) {
            $h = (int) round($height * $w / $width);
            $copy = $w === $width ? $image : self::resize($image, $w, $h);
            $path = "{$base}-{$w}.{$ext}";
            $disk->put($path, self::encode($copy, $useWebp));
            if ($copy !== $image) {
                imagedestroy($copy);
            }
            $files[] = ['w' => $w, 'h' => $h, 'path' => $path];
        }
        imagedestroy($image);

        $main = array_pop($files);

        return Media::create([
            'title' => $attributes['title'] ?? self::titleFrom($originalName),
            'alt_fa' => $attributes['alt_fa'] ?? null,
            'alt_en' => $attributes['alt_en'] ?? null,
            'path' => $main['path'],
            'variants' => array_map(fn (array $f) => ['w' => $f['w'], 'path' => $f['path']], $files),
            'width' => $main['w'],
            'height' => $main['h'],
            'size' => (int) $disk->size($main['path']),
            'mime' => $useWebp ? 'image/webp' : 'image/jpeg',
            'original_name' => $originalName ? Str::limit($originalName, 250, '') : null,
            'uploaded_by' => $attributes['uploaded_by'] ?? auth()->id(),
        ]);
    }

    private static function load(string $path, string $mime): GdImage
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
        };

        if (! $image instanceof GdImage) {
            throw new RuntimeException('این فایل خوانده نشد؛ ممکن است خراب باشد.');
        }

        // Palette images (GIF/8-bit PNG) → true color, keeping transparency.
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /** Phone cameras store photos sideways and record the rotation in EXIF; apply it. */
    private static function fixOrientation(GdImage $image, string $path, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private static function resize(GdImage $image, int $w, int $h): GdImage
    {
        $copy = imagecreatetruecolor($w, $h);
        imagealphablending($copy, false);
        imagesavealpha($copy, true);
        imagefill($copy, 0, 0, imagecolorallocatealpha($copy, 0, 0, 0, 127));
        imagecopyresampled($copy, $image, 0, 0, 0, 0, $w, $h, imagesx($image), imagesy($image));

        return $copy;
    }

    private static function encode(GdImage $image, bool $webp): string
    {
        ob_start();
        if ($webp) {
            imagewebp($image, null, self::QUALITY);
        } else {
            // No WebP support in this PHP build: JPEG on a white background.
            $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
            imagealphablending($flat, true);
            imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagejpeg($flat, null, self::QUALITY);
            imagedestroy($flat);
        }

        return (string) ob_get_clean();
    }

    private static function bytes(string $value): int
    {
        $number = (int) $value;

        return match (strtolower(substr(trim($value), -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /** "My Photo (1).JPG" → "my-photo-1-k3x9q2" (unique, safe for URLs). */
    private static function baseName(?string $originalName): string
    {
        $name = Str::slug(pathinfo((string) $originalName, PATHINFO_FILENAME));
        $name = $name !== '' ? Str::limit($name, 40, '') : 'image';

        return $name.'-'.Str::lower(Str::random(6));
    }

    private static function titleFrom(?string $originalName): ?string
    {
        $name = trim(preg_replace('/[_\-]+/', ' ', pathinfo((string) $originalName, PATHINFO_FILENAME)) ?? '');

        return $name !== '' ? Str::limit($name, 120, '') : null;
    }
}
