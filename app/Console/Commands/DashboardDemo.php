<?php

namespace App\Console\Commands;

use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\TrackingStage;
use Illuminate\Console\Command;

/**
 * Sample data so the dashboard charts can be tried before real sales are entered.
 * Every sample record is marked "[نمونه]" and is removed with --remove. Do not use on the live data.
 */
class DashboardDemo extends Command
{
    protected $signature = 'dashboard:demo {--remove : Delete the sample records again}';

    protected $description = 'Add (or remove) sample quotes and shipments with amounts for trying the dashboard';

    private const MARK = '[نمونه]';

    public function handle(): int
    {
        if ($this->option('remove')) {
            $shipments = Shipment::query()->where('customer_name', 'like', self::MARK.'%')->delete();
            $quotes = QuoteRequest::query()->where('name', 'like', self::MARK.'%')->delete();
            $this->info("Removed {$shipments} sample shipments and {$quotes} sample quotes.");

            return self::SUCCESS;
        }

        $routes = [
            ['air', 'گوانگجو', 'تهران', 18, 45], ['sea', 'شانگهای', 'بندرعباس', 60, 180], ['road', 'تهران', 'استانبول', 35, 90],
            ['road', 'تهران', 'مسکو', 50, 120], ['rail', 'تهران', 'آلماتی', 40, 95], ['air', 'دبی', 'تهران', 12, 30],
            ['sea', 'بندرعباس', 'هامبورگ', 90, 240], ['road', 'تهران', 'تاشکند', 30, 70],
        ];
        $stages = TrackingStage::ordered()->pluck('id')->all();
        $statuses = ['new', 'in_progress', 'quoted', 'won', 'won', 'lost'];
        $made = 0;

        for ($day = 180; $day >= 0; $day--) {
            $date = now()->subDays($day);
            for ($i = random_int(0, 2); $i > 0; $i--) {
                [$mode, $from, $to, $min, $max] = $routes[array_rand($routes)];

                $quote = new QuoteRequest([
                    'name' => self::MARK.' مشتری '.random_int(100, 999), 'phone' => '0912'.random_int(1000000, 9999999),
                    'transport_mode' => $mode, 'origin' => $from, 'destination' => $to, 'status' => $statuses[array_rand($statuses)],
                ]);
                $quote->created_at = $date;
                $quote->updated_at = $date;
                $quote->save();

                if ($quote->status !== 'won') {
                    continue;
                }
                // Prices grow a little over time; costs are 70–90% of the price.
                $invoice = random_int($min, $max) * 1_000_000 * (1 + (180 - $day) / 900);
                $shipment = new Shipment([
                    'quote_request_id' => $quote->id, 'customer_name' => $quote->name, 'transport_mode' => $mode,
                    'origin' => $from, 'destination' => $to,
                    'invoice_amount' => (int) round($invoice, -5),
                    'cost_amount' => (int) round($invoice * random_int(70, 90) / 100, -5),
                    'paid_amount' => $day > 30 ? (int) round($invoice, -5) : (int) round($invoice * random_int(0, 100) / 100, -5),
                    'invoiced_at' => $date->toDateString(),
                ]);
                $shipment->current_stage_id = $stages ? $stages[$day > 20 ? array_key_last($stages) : array_rand($stages)] : null;
                $shipment->created_at = $date;
                $shipment->updated_at = $date;
                $shipment->save();
                $made++;
            }
        }

        $this->info("Added sample quotes and {$made} sample shipments over the last 6 months. Remove them with: php artisan dashboard:demo --remove");

        return self::SUCCESS;
    }
}
