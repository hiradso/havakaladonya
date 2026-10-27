<?php

namespace App\Console\Commands;

use App\Support\Rates\ExchangeRates;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    protected $signature = 'rates:update';

    protected $description = 'Fetch the latest currency rates (toman) for the panel dashboard';

    public function handle(ExchangeRates $rates): int
    {
        $source = $rates->update();

        if ($source === null) {
            $this->warn('No source answered; the previous rates are kept.');

            return self::FAILURE;
        }

        $this->info('Rates updated from '.ExchangeRates::SOURCES[$source].'.');

        return self::SUCCESS;
    }
}
