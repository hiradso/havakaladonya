<?php

namespace Tests\Feature;

use App\Filament\Pages\Content\ContactContent;
use App\Filament\Pages\Content\HomeContent;
use App\Models\Media;
use App\Models\User;
use App\Support\SiteContent;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_match_the_original_site(): void
    {
        $fa = SiteContent::forFrontend('fa');

        $this->assertSame('از هر مبدأ،', $fa['texts']['home']['heroTitleA']);
        $this->assertSame('tel:+982188015232', $fa['contact']['phone']['href']);
        $this->assertSame('https://wa.me/989123336855', $fa['contact']['whatsapp']);
        $this->assertCount(3, $fa['team']);
        $this->assertSame('/images/photos/port-plane-1600.webp', $fa['images']['homeHero']['src']);
    }

    public function test_phone_numbers_become_international_links(): void
    {
        $this->assertSame('+989123336855', SiteContent::international('۰۹۱۲ ۳۳۳ ۶۸۵۵'));
        $this->assertSame('+442079460958', SiteContent::international('+44 20 7946 0958'));
        $this->assertSame('', SiteContent::international(''));
    }

    public function test_only_the_general_manager_can_edit(): void
    {
        foreach (['home', 'about', 'contact', 'footer'] as $page) {
            $this->actingAs(User::factory()->create(['role' => 'admin']))->get("/admin/content/{$page}")->assertOk();
            $this->actingAs(User::factory()->create(['role' => 'writer']))->get("/admin/content/{$page}")->assertForbidden();
        }
    }

    public function test_saved_texts_and_switches_reach_the_website(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Livewire::test(HomeContent::class)
            ->set('data.fa.home.heroTitleA', 'عنوان تازه')
            ->set('data.show.stats', false)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(ContactContent::class)
            ->set('data.phone', '021 1234 5678')
            ->set('data.instagram', '@newpage')
            ->call('save')
            ->assertHasNoErrors();

        $fa = SiteContent::forFrontend('fa');
        $this->assertSame('عنوان تازه', $fa['texts']['home']['heroTitleA']);
        $this->assertFalse($fa['show']['home']['stats']);
        $this->assertTrue($fa['show']['home']['cta']);
        $this->assertSame('tel:+982112345678', $fa['contact']['phone']['href']);
        $this->assertSame('https://instagram.com/newpage', $fa['contact']['instagram']['href']);

        SiteContent::reset('home');
        $this->assertSame('از هر مبدأ،', SiteContent::forFrontend('fa')['texts']['home']['heroTitleA']);
    }

    public function test_invalid_email_is_rejected(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Livewire::test(ContactContent::class)->set('data.email', 'not-an-email')->call('save')->assertHasErrors();
    }

    public function test_import_command_links_built_in_photos_once(): void
    {
        Storage::fake('public');
        $this->artisan('content:import-images')->assertSuccessful();
        $this->artisan('content:import-images')->assertSuccessful();

        $this->assertSame(5, Media::count());
        $this->assertNotNull(SiteContent::get('home')['hero_media']);
        $this->assertStringContainsString('/storage/media/', SiteContent::forFrontend('fa')['images']['homeHero']['src']);
    }
}
