<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable()->unique();
            $table->string('locale', 5)->default('fa');

            // Contact
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone', 32);
            $table->string('email')->nullable();

            // Shipment
            $table->string('transport_mode', 20);
            $table->string('origin');
            $table->string('destination');
            $table->string('cargo_type')->nullable();
            $table->decimal('weight_kg', 12, 2)->nullable();
            $table->decimal('volume_cbm', 12, 2)->nullable();
            $table->string('load_type', 20)->nullable();
            $table->boolean('needs_clearance')->default(false);
            $table->boolean('needs_insurance')->default(false);
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();

            // Internal (admin panel)
            $table->string('status', 20)->default('new')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
