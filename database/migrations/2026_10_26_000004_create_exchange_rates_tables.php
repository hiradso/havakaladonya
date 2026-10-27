<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Latest rate of each currency in toman (one row per currency, overwritten on every update).
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->string('code', 10)->primary();       // usd, eur, aed ...
            $table->decimal('price', 16, 2);             // toman for 1 unit
            $table->decimal('change', 16, 2)->nullable(); // toman, against the previous day's close
            $table->decimal('change_percent', 8, 2)->nullable();
            $table->decimal('high', 16, 2)->nullable();
            $table->decimal('low', 16, 2)->nullable();
            $table->string('source', 20);                 // tgju | navasan
            $table->timestamp('quoted_at')->nullable();   // time of the price at the source (Tehran)
            $table->timestamp('fetched_at');              // when our server got it
        });

        // Hourly snapshots for the little trend lines (kept 30 days).
        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->decimal('price', 16, 2);
            $table->timestamp('recorded_at');
            $table->index(['code', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
        Schema::dropIfExists('exchange_rates');
    }
};
