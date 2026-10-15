<?php

use App\Http\Controllers\QuoteRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
| Public website — the same pages in two languages:
|   Persian:  /            /services   /tracking ...
|   English:  /en          /en/services ...
*/
$publicPages = function (string $locale) {
    Route::get('/', fn () => Inertia::render('home'))->name("{$locale}.home");

    // Quote request form
    Route::get('quote', [QuoteRequestController::class, 'create'])->name("{$locale}.quote");
    Route::post('quote', [QuoteRequestController::class, 'store'])
        ->middleware('throttle:5,10') // max 5 requests per 10 minutes per visitor
        ->name("{$locale}.quote.store");
    Route::get('quote/thanks', [QuoteRequestController::class, 'thanks'])->name("{$locale}.quote.thanks");

    // Pages that are not built yet show a "coming soon" screen instead of a 404.
    foreach (['services', 'tracking', 'about', 'blog', 'contact'] as $page) {
        Route::get($page, fn () => Inertia::render('coming-soon', ['page' => $page]))->name("{$locale}.{$page}");
    }
};

Route::middleware('locale:fa')->group(fn () => $publicPages('fa'));
Route::prefix('en')->middleware('locale:en')->group(fn () => $publicPages('en'));

// Starter-kit routes kept for now (customer accounts come in phase 2).
Route::get('/home', fn () => redirect('/'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
