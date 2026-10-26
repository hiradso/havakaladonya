<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('alt_fa')->nullable();
            $table->string('alt_en')->nullable();
            // Main (largest) optimized file on the public disk, e.g. media/2026/10/abc123-1920.webp
            $table->string('path');
            // Smaller copies for phones/tablets: [{"w": 640, "path": "..."}, ...] (ascending)
            $table->json('variants')->nullable();
            $table->unsignedInteger('width')->default(0);
            $table->unsignedInteger('height')->default(0);
            $table->unsignedInteger('size')->default(0); // bytes of the main file
            $table->string('mime', 50)->default('image/webp');
            $table->string('original_name')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
