<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\TrackingController;
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

    // Shipment tracking: /tracking?code=HKD-XXXXXXXX (rate-limited inside the controller)
    Route::get('tracking', [TrackingController::class, 'show'])->name("{$locale}.tracking");

    // Company pages
    Route::get('services', [PageController::class, 'services'])->name("{$locale}.services");
    Route::get('services/{slug}', [PageController::class, 'service'])->name("{$locale}.service");
    Route::get('about', [PageController::class, 'about'])->name("{$locale}.about");
    Route::get('contact', [ContactController::class, 'show'])->name("{$locale}.contact");
    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,10') // max 5 messages per 10 minutes per visitor
        ->name("{$locale}.contact.store");

    // Pages that are not built yet show a "coming soon" screen instead of a 404.
    foreach (['blog'] as $page) {
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

// Old WordPress addresses (e.g. /حمل-و-نقل-هوایی/) → new pages with a permanent redirect.
Route::fallback([PageController::class, 'legacy'])->middleware('locale:fa');
