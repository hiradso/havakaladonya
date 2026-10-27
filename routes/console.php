<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Currency rates for the panel dashboard (needs the server cron: * * * * * php artisan schedule:run).
Schedule::command('rates:update')->everyFiveMinutes()->withoutOverlapping(10);
