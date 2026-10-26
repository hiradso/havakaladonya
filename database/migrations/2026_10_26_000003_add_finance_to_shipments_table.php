<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // All amounts in Toman. Only the general manager and finance see these.
            $table->unsignedBigInteger('invoice_amount')->nullable()->after('current_stage_id'); // charged to the customer
            $table->unsignedBigInteger('cost_amount')->nullable()->after('invoice_amount');       // what the shipment cost us
            $table->unsignedBigInteger('paid_amount')->default(0)->after('cost_amount');          // received so far
            $table->date('invoiced_at')->nullable()->index()->after('paid_amount');               // date of the sale (charts)
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['invoice_amount', 'cost_amount', 'paid_amount', 'invoiced_at']);
        });
    }
};
