<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Service;
use App\Support\Services;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('home', [
            'posts' => Post::published()
                ->where('locale', app()->getLocale())
                ->latest('published_at')
                ->limit(4)
                ->get()
                ->map->toCard()
                ->values(),
            'services' => $this->serviceCards(),
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('services', ['services' => $this->serviceCards()]);
    }

    public function service(string $slug): Response
    {
        $service = Service::active()->with('media')->where('slug', $slug)->firstOrFail();

        return Inertia::render('service', [
            'service' => $service->toPage(app()->getLocale()),
            'others' => array_values(array_filter($this->serviceCards(), fn (array $s) => $s['slug'] !== $service->slug)),
        ]);
    }

    /** Active services for lists, in the order set in the panel. */
    private function serviceCards(): array
    {
        $locale = app()->getLocale();

        return Service::active()->get()->map(fn (Service $s) => $s->toCard($locale))->all();
    }

    public function about(): Response
    {
        return Inertia::render('about');
    }

    /** Old WordPress addresses → new pages; anything else is a normal 404. */
    public function legacy(Request $request): RedirectResponse
    {
        $path = trim(rawurldecode($request->path()), '/');
        $english = str_starts_with($path.'/', 'en/');
        $key = $english ? substr($path, 3) : $path;

        if ($target = Services::LEGACY_REDIRECTS[$key] ?? null) {
            return redirect($english ? '/en'.$target : $target, 301);
        }

        // Imported blog articles remember their old address.
        $post = Post::published()->where('legacy_path', $path)->first();
        abort_if($post === null, 404);

        return redirect($post->url(), 301);
    }
}
