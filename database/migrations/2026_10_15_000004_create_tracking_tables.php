<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Steps a shipment goes through. Editable in the admin panel.
        Schema::create('tracking_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name_fa');
            $table->string('name_en');
            $table->unsignedInteger('sort')->default(0)->index();
            $table->boolean('is_final')->default(false); // e.g. "Delivered"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code', 20)->unique();
            $table->foreignId('quote_request_id')->nullable()->constrained()->nullOnDelete();

            // Internal (never shown on the public tracking page)
            $table->string('customer_name');
            $table->string('customer_phone', 32)->nullable();
            $table->text('internal_notes')->nullable();

            // Shown to the customer
            $table->string('transport_mode', 20);
            $table->string('origin');
            $table->string('destination');
            $table->string('cargo_description')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->foreignId('current_stage_id')->nullable()->constrained('tracking_stages')->nullOnDelete();

            $table->timestamps();
        });

        // Each step reached, with an optional note the customer can read.
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tracking_stage_id')->constrained()->restrictOnDelete();
            $table->string('location')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('happened_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['shipment_id', 'happened_at']);
        });

        $now = now();
        $stages = [
            ['ثبت سفارش', 'Order registered', false],
            ['تحویل از فرستنده', 'Picked up from shipper', false],
            ['در مسیر', 'In transit', false],
            ['رسیدن به مرز / بندر', 'Arrived at border / port', false],
            ['در حال ترخیص', 'Customs clearance', false],
            ['تحویل شده', 'Delivered', true],
        ];

        foreach ($stages as $i => [$fa, $en, $final]) {
            DB::table('tracking_stages')->insert([
                'name_fa' => $fa,
                'name_en' => $en,
                'sort' => $i + 1,
                'is_final' => $final,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('tracking_stages');
    }
};
