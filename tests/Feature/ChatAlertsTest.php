<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewChatMessage;
use App\Support\ChatPresence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ChatAlertsTest extends TestCase
{
    use RefreshDatabase;

    private function team(): array
    {
        return [
            User::factory()->create(['role' => 'admin']),
            User::factory()->create(['role' => 'sales_ops']),
            User::factory()->create(['role' => 'writer']),
        ];
    }

    private function startChat(): string
    {
        return $this->postJson('/chat/start', ['name' => 'علی', 'phone' => '09120000000', 'message' => 'سلام'])
            ->assertCreated()->json('token');
    }

    public function test_team_gets_an_email_when_nobody_is_online(): void
    {
        Notification::fake();
        [$admin, $sales, $writer] = $this->team();

        $token = $this->startChat();
        Notification::assertSentTo([$admin, $sales], NewChatMessage::class, fn ($n) => $n->isNew);
        Notification::assertNotSentTo($writer, NewChatMessage::class);

        // A follow-up within the cool-down window does not send a second email.
        $this->postJson("/chat/{$token}/messages", ['body' => 'هستید؟'])->assertCreated();
        Notification::assertSentToTimes($admin, NewChatMessage::class, 1);
    }

    public function test_no_email_when_someone_has_the_panel_open(): void
    {
        Notification::fake();
        $this->team();
        ChatPresence::ping();

        $this->startChat();
        Notification::assertNothingSent();
    }

    public function test_calculator_page_opens_for_every_staff_role(): void
    {
        foreach (['admin', 'sales_ops', 'finance', 'writer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/admin/calculator')
                ->assertOk()
                ->assertSee('ابزار محاسبه')
                ->assertSee('hkdCalculator', false);
        }
    }
}
