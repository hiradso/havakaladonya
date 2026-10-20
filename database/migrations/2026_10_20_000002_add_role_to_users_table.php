<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff role in the admin panel: admin | sales_ops | finance | writer.
        // Empty = not staff (no panel access), e.g. future customer accounts.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->nullable()->after('email')->index();
        });

        // Everyone who could open the panel until now becomes general manager.
        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
