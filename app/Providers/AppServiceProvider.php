<?php

namespace App\Providers;

use App\Support\Digits;
use App\Support\Jalali;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Numbers inside Persian validation messages in Persian digits ("حداقل ۸ حرف", not "8").
        foreach (['min' => ['min'], 'max' => ['max'], 'size' => ['size'], 'between' => ['min', 'max']] as $rule => $placeholders) {
            Validator::replacer($rule, function (string $message, string $attribute, string $r, array $parameters) use ($placeholders): string {
                foreach ($placeholders as $i => $placeholder) {
                    $value = (string) ($parameters[$i] ?? '');
                    $message = str_replace(':'.$placeholder, App::getLocale() === 'fa' ? Digits::toPersian($value) : $value, $message);
                }

                return $message;
            });
        }

        // Times are stored in UTC; the admin panel shows and edits them in Tehran time.
        FilamentTimezone::set('Asia/Tehran');

        // ->jalali() on a table column shows the date in the Persian calendar: ۱۴۰۵/۰۷/۲۸
        TextColumn::macro('jalali', function (bool $withTime = false): TextColumn {
            /** @var TextColumn $this */
            return $this->formatStateUsing(fn ($state) => Jalali::format($state, $withTime));
        });

        // Separate limits per form/feature. (A plain "throttle:5,10" shares one counter per visitor
        // across all routes, so chat polling would block the quote form.)
        $limit = fn (string $name, Limit $limit) => RateLimiter::for($name, fn (Request $r) => $limit->by($name.'|'.$r->ip()));
        $limit('quote', Limit::perMinutes(10, 5));
        $limit('contact', Limit::perMinutes(10, 5));
        $limit('chat-start', Limit::perMinutes(10, 5));
        $limit('chat-send', Limit::perMinute(20));
        $limit('chat-poll', Limit::perMinute(120));
    }
}
