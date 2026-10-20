<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessage;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('contact', [
            'sent' => (bool) session('contact_sent'),
        ]);
    }

    public function store(StoreContactMessage $request): RedirectResponse
    {
        $message = ContactMessage::create([
            ...$request->safe()->except('website'),
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        // A mail problem must never lose the customer's message.
        try {
            Notification::send(User::whereIn('role', \App\Support\Roles::CUSTOMER_TEAM)->get(), new NewContactMessage($message));
        } catch (Throwable $e) {
            Log::error('Contact notification failed', ['message' => $message->id, 'error' => $e->getMessage()]);
        }

        return back()->with('contact_sent', true);
    }
}
