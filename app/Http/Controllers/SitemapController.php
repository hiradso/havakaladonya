<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Services;
use Illuminate\Http\Response;

/**
 * /sitemap.xml — every public page in both languages (linked to each other with hreflang)
 * plus every published article. Search engines read it to find and refresh pages.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $pages = ['/', '/services', '/about', '/contact', '/blog', '/quote', '/tracking'];
        foreach (Services::SLUGS as $slug) {
            $pages[] = "/services/{$slug}";
        }

        $urls = [];
        foreach ($pages as $path) {
            $fa = $base.$path;
            $en = $base.($path === '/' ? '/en' : "/en{$path}");
            $alternates = ['fa' => $fa, 'en' => $en, 'x-default' => $fa];
            $priority = $path === '/' ? '1.0' : (str_starts_with($path, '/services') ? '0.9' : '0.7');

            $urls[] = ['loc' => $fa, 'alternates' => $alternates, 'priority' => $priority];
            $urls[] = ['loc' => $en, 'alternates' => $alternates, 'priority' => $priority];
        }

        Post::published()->orderByDesc('published_at')->get(['locale', 'slug', 'updated_at'])
            ->each(function (Post $post) use (&$urls, $base): void {
                $urls[] = [
                    'loc' => $base.$post->url(),
                    'lastmod' => $post->updated_at?->toAtomString(),
                    'priority' => '0.6',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n    <loc>".e($url['loc'])."</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            foreach ($url['alternates'] ?? [] as $lang => $href) {
                $xml .= '    <xhtml:link rel="alternate" hreflang="'.$lang.'" href="'.e($href).'"/>'."\n";
            }
            $xml .= "    <priority>{$url['priority']}</priority>\n  </url>\n";
        }

        $xml .= "</urlset>\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
