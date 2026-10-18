<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->where('locale', app()->getLocale())
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('blog', [
            'posts' => collect($posts->items())->map->toCard()->values(),
            'pagination' => [
                'current' => $posts->currentPage(),
                'last' => $posts->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $locale = app()->getLocale();
        $post = Post::published()->where('locale', $locale)->where('slug', $slug)->firstOrFail();

        $related = Post::published()
            ->where('locale', $locale)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map->toCard()
            ->values();

        return Inertia::render('post', [
            'post' => [
                ...$post->toCard(),
                // Sanitized: only safe tags and attributes reach the page.
                'body' => $post->renderedBody(),
                'metaTitle' => $post->localDigits($post->meta_title ?: $post->title),
                'metaDescription' => $post->meta_description ?: Str::limit($post->toCard()['excerpt'], 160),
            ],
            'related' => $related,
            // Language switch on an article goes to the other language's blog.
            'alternateUrl' => $locale === 'en' ? '/blog' : '/en/blog',
        ]);
    }
}
