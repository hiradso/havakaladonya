<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('icon', 40)->default('package');
            $table->string('quote_mode', 10)->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            // Built-in photo name (public/images/photos/{name}.webp) until one is chosen from the media library.
            $table->string('photo_default')->nullable();
            // {"fa": {"title", "short", "lead", "body": [], "features": [], "groups": [], "chips": {}, "metaTitle", "metaDescription"}, "en": {...}}
            $table->json('content');
            $table->timestamps();
        });

        // The nine services of the original site (texts from resources/content/services.json).
        $services = json_decode((string) file_get_contents(resource_path('content/services.json')), true) ?: [];
        foreach ($services as $service) {
            DB::table('services')->insert([
                'slug' => $service['slug'],
                'sort' => $service['sort'],
                'is_active' => true,
                'icon' => $service['icon'],
                'quote_mode' => $service['quote_mode'],
                'photo_default' => $service['photo_default'],
                'content' => json_encode($service['content'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
