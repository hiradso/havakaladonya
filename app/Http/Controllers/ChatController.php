<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Support\ChatAlerts;
use App\Support\ChatPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Live chat for website visitors (JSON, used by the chat widget).
 * A visitor is identified only by the random token their browser keeps; no account needed.
 */
class ChatController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json(['online' => ChatPresence::online()]);
    }

    public function start(Request $request): JsonResponse
    {
        $request->merge(['phone' => $this->cleanPhone($request->input('phone'))]);
        $fa = $request->input('locale') !== 'en';

        $data = $request->validate([
            'website' => ['prohibited'], // honeypot
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'message' => ['required', 'string', 'max:2000'],
            'locale' => ['nullable', 'in:fa,en'],
            'page' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => $fa ? 'نام خود را وارد کنید.' : 'Please enter your name.',
            'phone.required' => $fa ? 'شماره تماس را وارد کنید.' : 'Please enter your phone number.',
            'phone.regex' => $fa ? 'شماره تماس معتبر نیست.' : 'Please enter a valid phone number.',
            'message.required' => $fa ? 'پیام خود را بنویسید.' : 'Please write your message.',
        ]);

        $conversation = ChatConversation::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'locale' => $data['locale'] ?? 'fa',
            'page_url' => $data['page'] ?? null,
            'ip_address' => $request->ip(),
        ]);
        $conversation->addMessage('visitor', trim($data['message']));
        ChatAlerts::visitorWrote($conversation, trim($data['message']), isNew: true);

        return response()->json([
            'token' => $conversation->token,
            ...$this->payload($conversation->fresh()),
        ], 201);
    }

    public function show(Request $request, string $token): JsonResponse
    {
        $conversation = $this->find($token);
        $after = (int) $request->query('after', 0);

        $update = ['visitor_seen_at' => now()];
        if ($request->boolean('read')) {
            // The chat window is open, so the visitor has seen all messages.
            $update['visitor_read_id'] = (int) $conversation->messages()->max('id');
        }
        $conversation->forceFill($update)->saveQuietly();

        return response()->json($this->payload($conversation, $after));
    }

    public function send(Request $request, string $token): JsonResponse
    {
        $conversation = $this->find($token);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->addMessage('visitor', trim($data['body']));
        ChatAlerts::visitorWrote($conversation, trim($data['body']), isNew: false);

        return response()->json(['message' => $message->toPublic()], 201);
    }

    private function find(string $token): ChatConversation
    {
        return ChatConversation::where('token', $token)->firstOrFail();
    }

    private function payload(ChatConversation $conversation, int $after = 0): array
    {
        $messages = $conversation->messages()->where('id', '>', $after)->limit(200)->get();

        return [
            'name' => $conversation->name,
            'status' => $conversation->status,
            'online' => ChatPresence::online(),
            'unread' => $conversation->messages()->where('sender', 'agent')->where('id', '>', $conversation->visitor_read_id)->count(),
            'messages' => $messages->map->toPublic()->values(),
        ];
    }

    private function cleanPhone(mixed $phone): ?string
    {
        if (! is_string($phone)) {
            return null;
        }

        $phone = strtr(trim($phone), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        return preg_replace('/[\s\-()]/', '', $phone);
    }
}
