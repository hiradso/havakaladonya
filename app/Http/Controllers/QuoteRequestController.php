<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\NewQuoteRequest;
use App\Support\QuoteForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class QuoteRequestController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('quote', [
            'form' => QuoteForm::config(),
        ]);
    }

    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['attachment', 'website']);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // Stored on the private "local" disk: not reachable from the web, only via the admin panel.
            $data['attachment_path'] = $file->store('quote-attachments', 'local');
            $data['attachment_name'] = mb_substr($file->getClientOriginalName(), 0, 200);
        }

        $quote = QuoteRequest::create([
            ...$data,
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        // A mail problem must never lose the customer's request.
        try {
            Notification::send(User::whereIn('role', \App\Support\Roles::CUSTOMER_TEAM)->get(), new NewQuoteRequest($quote->fresh()));
        } catch (Throwable $e) {
            Log::error('Quote notification failed', ['quote' => $quote->id, 'error' => $e->getMessage()]);
        }

        $thanks = app()->getLocale() === 'en' ? '/en/quote/thanks' : '/quote/thanks';

        return redirect($thanks)->with('quote_reference', $quote->fresh()->reference);
    }

    public function thanks(): Response
    {
        return Inertia::render('quote-thanks', [
            'reference' => session('quote_reference'),
        ]);
    }
}
