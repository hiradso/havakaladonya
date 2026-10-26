<?php

namespace Tests\Feature;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_original_services_are_seeded_and_shown(): void
    {
        $this->assertSame(9, Service::count());
        $this->get('/services')->assertOk();
        $this->get('/services/air-freight')->assertOk();
        $this->get('/en/services/customs-clearance')->assertOk();
        $this->get('/services/unknown')->assertNotFound();
    }

    public function test_hidden_service_is_not_reachable(): void
    {
        Service::where('slug', 'consulting')->update(['is_active' => false]);

        $this->get('/services/consulting')->assertNotFound();
        $this->assertStringNotContainsString('/services/consulting', $this->get('/sitemap.xml')->getContent());
    }

    public function test_only_the_general_manager_can_manage_services(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/services')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'writer']))->get('/admin/services')->assertForbidden();
    }

    public function test_edit_keeps_other_content_and_slug(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $service = Service::where('slug', 'customs-clearance')->first();

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->set('data.content.fa.title', 'ترخیص سریع کالا')
            ->call('save')
            ->assertHasNoErrors();

        $service->refresh();
        $this->assertSame('ترخیص سریع کالا', $service->content['fa']['title']);
        $this->assertNotEmpty($service->content['fa']['groups']);
        $this->assertSame('customs-clearance', $service->slug);
    }

    public function test_new_service_gets_slug_from_english_title_and_goes_last(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Livewire::test(CreateService::class)
            ->set('data.content.fa.title', 'انبارداری')
            ->set('data.content.en.title', 'Warehousing')
            ->set('data.icon', 'warehouse')
            ->call('create')
            ->assertHasNoErrors();

        $service = Service::latest('id')->first();
        $this->assertSame('warehousing', $service->slug);
        $this->assertSame(10, $service->sort);
        $this->get('/services/warehousing')->assertOk();
    }
}
