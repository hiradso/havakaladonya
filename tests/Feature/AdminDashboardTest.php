<?php

namespace Tests\Feature;

use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Models\Shipment;
use App\Models\User;
use App\Support\Dashboard\Metrics;
use App\Support\JalaliCalendar;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_jalali_conversion(): void
    {
        $this->assertSame([1405, 8, 4], JalaliCalendar::fromGregorian(2026, 10, 26));
        $this->assertSame([1403, 12, 30], JalaliCalendar::fromGregorian(2025, 3, 20));
        $this->assertSame([2026, 3, 21], JalaliCalendar::toGregorian(1405, 1, 1));
    }

    public function test_finance_totals_and_series(): void
    {
        Shipment::create(['customer_name' => 'A', 'transport_mode' => 'air', 'origin' => 'X', 'destination' => 'Y',
            'invoice_amount' => 50_000_000, 'cost_amount' => 40_000_000, 'paid_amount' => 20_000_000, 'invoiced_at' => now()->toDateString()]);
        Shipment::create(['customer_name' => 'B', 'transport_mode' => 'sea', 'origin' => 'X', 'destination' => 'Z',
            'invoice_amount' => 30_000_000, 'cost_amount' => 25_000_000, 'paid_amount' => 30_000_000, 'invoiced_at' => now()->toDateString()]);

        $m = new Metrics(['period' => '30d']);
        $totals = $m->financeTotals();

        $this->assertSame('day', $m->grouping);
        $this->assertSame(80_000_000, $totals['revenue']);
        $this->assertSame(15_000_000, $totals['profit']);
        $this->assertSame(30_000_000, $totals['receivable']);
        $this->assertSame(80_000_000, array_sum($m->financeSeries()['revenue']));
        $this->assertSame(50_000_000, (new Metrics(['period' => '30d', 'mode' => 'air']))->financeTotals()['revenue']);
    }

    public function test_dashboard_shows_the_right_charts_per_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin')->assertOk()->assertSee('فروش، هزینه و سود');
        $this->actingAs(User::factory()->create(['role' => 'finance']))->get('/admin')->assertOk()->assertSee('فروش، هزینه و سود');
        $this->actingAs(User::factory()->create(['role' => 'sales_ops']))->get('/admin')->assertOk()
            ->assertSee('استعلام‌ها و محموله‌های جدید')->assertDontSee('فروش، هزینه و سود');
        $this->actingAs(User::factory()->create(['role' => 'writer']))->get('/admin')->assertOk()->assertDontSee('استعلام‌ها و محموله‌های جدید');
    }

    public function test_finance_can_only_change_money_fields(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $shipment = Shipment::create(['customer_name' => 'A', 'transport_mode' => 'air', 'origin' => 'Tehran', 'destination' => 'Dubai']);
        $this->actingAs(User::factory()->create(['role' => 'finance']));

        Livewire::test(EditShipment::class, ['record' => $shipment->getRouteKey()])
            ->set('data.invoice_amount', '25,000,000')
            ->set('data.cost_amount', '۱۸٬۰۰۰٬۰۰۰')
            ->set('data.origin', 'Changed')
            ->call('save')
            ->assertHasNoErrors();

        $shipment->refresh();
        $this->assertSame(25_000_000, $shipment->invoice_amount);
        $this->assertSame(18_000_000, $shipment->cost_amount);
        $this->assertSame('Tehran', $shipment->origin);
        $this->assertNotNull($shipment->invoiced_at);
    }

    public function test_sales_ops_does_not_see_money(): void
    {
        $shipment = Shipment::create(['customer_name' => 'A', 'transport_mode' => 'air', 'origin' => 'X', 'destination' => 'Y']);

        $this->actingAs(User::factory()->create(['role' => 'sales_ops']))
            ->get("/admin/shipments/{$shipment->id}/edit")
            ->assertOk()
            ->assertDontSee('مبلغ فاکتور');
    }

    public function test_demo_data_can_be_added_and_removed(): void
    {
        $this->artisan('dashboard:demo')->assertSuccessful();
        $this->assertGreaterThan(0, Shipment::where('customer_name', 'like', '[نمونه]%')->count());

        $this->artisan('dashboard:demo', ['--remove' => true])->assertSuccessful();
        $this->assertSame(0, Shipment::where('customer_name', 'like', '[نمونه]%')->count());
    }
}
