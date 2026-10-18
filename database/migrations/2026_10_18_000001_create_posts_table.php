<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Blog articles. Each article belongs to one language (fa or en).
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2)->default('fa');
            $table->string('title');
            $table->string('slug', 191);
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            // Address on the old WordPress site, used to redirect old links (e.g. "en/polish-manufacturers").
            $table->string('legacy_path', 191)->nullable()->index();
            $table->unsignedBigInteger('wordpress_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
