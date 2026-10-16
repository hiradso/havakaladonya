<?php

namespace App\Http\Controllers;

use App\Support\Services;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function services(): Response
    {
        return Inertia::render('services');
    }

    public function service(string $slug): Response
    {
        abort_unless(in_array($slug, Services::SLUGS, true), 404);

        return Inertia::render('service', ['slug' => $slug]);
    }

    public function about(): Response
    {
        return Inertia::render('about');
    }

    /** Old WordPress addresses → new pages; anything else is a normal 404. */
    public function legacy(Request $request): RedirectResponse
    {
        $path = trim(rawurldecode($request->path()), '/');
        $english = str_starts_with($path, 'en/');
        $key = $english ? substr($path, 3) : $path;

        $target = Services::LEGACY_REDIRECTS[$key] ?? null;
        abort_if($target === null, 404);

        return redirect($english ? '/en'.$target : $target, 301);
    }
}
