<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Copies the articles of the old WordPress site into the new blog:
 *   php artisan blog:import-wordpress
 *
 * - Persian and English articles (WPML), with title, text, excerpt, date and featured image
 * - Images inside the text are downloaded too, so nothing depends on the old site
 * - Each article remembers its old address, so old links redirect to the new page
 * - Running it again skips articles that were already imported
 */
class ImportWordPressPosts extends Command
{
    protected $signature = 'blog:import-wordpress
        {--url=https://havakaladonya.com : Address of the old WordPress site}
        {--draft : Import as drafts instead of published}
        {--refresh-images : Re-download missing images of already imported articles}';

    protected $description = 'Import blog articles from the old WordPress site';

    private string $base;

    private string $host;

    public function handle(): int
    {
        $this->base = rtrim((string) $this->option('url'), '/');
        $this->host = (string) parse_url($this->base, PHP_URL_HOST);

        $seen = [];
        $created = 0;
        $skipped = 0;
        $refreshed = 0;

        foreach (['fa', 'en'] as $lang) {
            $page = 1;

            do {
                $response = $this->get('/wp-json/wp/v2/posts', [
                    'lang' => $lang,
                    'per_page' => 50,
                    'page' => $page,
                    '_embed' => 1,
                ]);

                if (! $response || ! $response->ok()) {
                    // WordPress answers 400 when asking for a page after the last one.
                    break;
                }

                foreach ($response->json() as $item) {
                    if (isset($seen[$item['id']])) {
                        continue;
                    }
                    $seen[$item['id']] = true;

                    if ($existing = Post::where('wordpress_id', $item['id'])->first()) {
                        if ($this->option('refresh-images')) {
                            $this->refreshImages($existing, $item);
                            $refreshed++;
                            $this->info("  images refreshed: {$existing->title}");

                            continue;
                        }
                        $skipped++;
                        $this->line("  skipped (already imported): {$item['id']}");

                        continue;
                    }

                    try {
                        $post = $this->import($item);
                        $created++;
                        $this->info("  imported [{$post->locale}] {$post->title}");
                    } catch (Throwable $e) {
                        $this->error("  failed {$item['id']}: {$e->getMessage()}");
                    }
                }

                $totalPages = (int) $response->header('X-WP-TotalPages');
                $page++;
            } while ($page <= $totalPages);
        }

        $this->newLine();
        $this->info("Done: {$created} imported, {$skipped} already there, {$refreshed} refreshed.");

        if ($created === 0 && $skipped === 0 && $refreshed === 0) {
            $this->warn('No articles were found. Check that the old site is reachable: '.$this->base.'/wp-json/wp/v2/posts');
        }

        return self::SUCCESS;
    }

    private function import(array $item): Post
    {
        $link = (string) ($item['link'] ?? '');
        $path = trim(rawurldecode((string) parse_url($link, PHP_URL_PATH)), '/');
        $locale = str_starts_with($path.'/', 'en/') ? 'en' : 'fa';

        $title = $this->plain($item['title']['rendered'] ?? '');
        $excerpt = Str::limit(preg_replace('/\s*\[(…|&hellip;|\.\.\.)\]\s*$/u', '', $this->plain($item['excerpt']['rendered'] ?? '')) ?? '', 480);

        $cover = $this->downloadCover($item);

        return Post::create([
            'wordpress_id' => $item['id'],
            'locale' => $locale,
            'title' => $title ?: 'بدون عنوان',
            'slug' => rawurldecode((string) ($item['slug'] ?? '')) ?: $title,
            'excerpt' => $excerpt ?: null,
            'body' => $this->cleanBody((string) ($item['content']['rendered'] ?? '')),
            'cover_path' => $cover,
            'status' => $this->option('draft') ? 'draft' : 'published',
            'published_at' => isset($item['date_gmt']) ? \Illuminate\Support\Carbon::parse($item['date_gmt'], 'UTC') : now(),
            'legacy_path' => $path ?: null,
        ]);
    }

    /** Fills in the featured image and text images that failed to download last time. */
    private function refreshImages(Post $post, array $item): void
    {
        $changes = [];

        if (! $post->cover_path) {
            $changes['cover_path'] = $this->downloadCover($item);
        }

        // Only rebuild the text when nobody has edited it in the panel since the import.
        if ($post->updated_at->equalTo($post->created_at)) {
            $changes['body'] = $this->cleanBody((string) ($item['content']['rendered'] ?? ''));
        }

        if ($changes) {
            $post->forceFill(array_filter($changes, fn ($v) => $v !== null))->saveQuietly();
        }
    }

    /**
     * Featured image: the original first, then the smaller copies WordPress made of it
     * (some originals with Persian names are not served by the old host).
     */
    private function downloadCover(array $item): ?string
    {
        $media = $item['_embedded']['wp:featuredmedia'][0] ?? null;
        if (! $media) {
            return null;
        }

        $sizes = $media['media_details']['sizes'] ?? [];
        $candidates = array_filter([
            $media['source_url'] ?? null,
            ...array_map(fn ($key) => $sizes[$key]['source_url'] ?? null, ['large', 'us_1024_0', 'us_768_0', 'medium_large', 'us_600_0', 'medium']),
        ]);

        foreach (array_unique($candidates) as $url) {
            if ($path = $this->download($url, 'blog/covers', quiet: true)) {
                return $path;
            }
        }

        $this->warn('    featured image not downloaded: '.($media['source_url'] ?? '?'));

        return null;
    }

    /** Removes WordPress leftovers and copies images inside the text to this site. */
    private function cleanBody(string $html): string
    {
        // Shortcodes like [vc_row] or [caption …]
        $html = preg_replace('/\[\/?[a-z_\-]+[^\]]*\]/i', '', $html) ?? $html;

        // Images: download and point to the copy; drop responsive-size attributes of the old site.
        $html = preg_replace_callback('/<img\b[^>]*>/i', function (array $m): string {
            if (! preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $m[0], $src)) {
                return '';
            }
            preg_match('/\balt\s*=\s*["\']([^"\']*)["\']/i', $m[0], $alt);
            $local = $this->download(html_entity_decode($src[1]), 'blog/content');

            if (! $local) {
                return '';
            }

            return '<img src="/storage/'.$local.'" alt="'.e(html_entity_decode($alt[1] ?? '')).'">';
        }, $html) ?? $html;

        // Links to the old site's own pages become relative, so redirects take them to the new pages.
        $html = preg_replace('#href=(["\'])https?://(www\.)?'.preg_quote($this->host, '#').'#i', 'href=$1', $html) ?? $html;

        // Inline styles and classes from the old theme
        $html = preg_replace('/\s(style|class|id|width|height|srcset|sizes|loading|decoding)=("[^"]*"|\'[^\']*\')/i', '', $html) ?? $html;

        // Empty paragraphs
        $html = preg_replace('#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '', $html) ?? $html;

        return trim($html);
    }

    /** Downloads a file from the old site to storage/app/public/{dir}. Returns the stored path or null. */
    private function download(string $url, string $dir, bool $quiet = false): ?string
    {
        if (str_starts_with($url, '/')) {
            $url = $this->base.$url;
        }

        // Only copy files that live on the old site itself.
        $host = (string) parse_url($url, PHP_URL_HOST);
        if ($host !== $this->host && $host !== 'www.'.$this->host) {
            return null;
        }

        // Persian file names (and invisible direction marks) must be percent-encoded, once.
        $rawPath = rawurldecode((string) parse_url($url, PHP_URL_PATH));
        $url = (string) preg_replace('#^(https?://[^/]+).*$#', '$1', $url)
            .implode('/', array_map('rawurlencode', explode('/', $rawPath)));

        $name = Str::limit(Str::slug(pathinfo($rawPath, PATHINFO_FILENAME)) ?: 'image', 60, '');
        $ext = strtolower(pathinfo($rawPath, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return null;
        }

        $path = "{$dir}/{$name}-".substr(md5($url), 0, 8).".{$ext}";
        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            $response = Http::timeout(60)->retry(2, 1000, throw: false)->get($url);
        } catch (Throwable) {
            $response = null;
        }

        if (! $response || ! $response->ok() || strlen($response->body()) === 0) {
            if (! $quiet) {
                $this->warn('    image not downloaded: '.rawurldecode($url));
            }

            return null;
        }

        Storage::disk('public')->put($path, $response->body());

        return $path;
    }

    private function get(string $endpoint, array $query = []): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(60)->acceptJson()->retry(2, 1000, throw: false)->get($this->base.$endpoint, $query);
        } catch (Throwable $e) {
            $this->error('Could not reach the old site: '.$e->getMessage());

            return null;
        }
    }

    private function plain(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }
}
