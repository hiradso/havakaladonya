<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the site language from the route: "/..." is Persian, "/en/..." is English.
 * Usage in routes: ->middleware('locale:en')
 */
class SetLocale
{
    public const SUPPORTED = ['fa', 'en'];

    public function handle(Request $request, Closure $next, string $locale = 'fa'): Response
    {
        app()->setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
