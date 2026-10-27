<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Free videos / tips: open to everyone, no login or subscription needed.
     */
    public function up(): void
    {
        Schema::create('free_videos', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable(); // Thumbnail (the YouTube one is used when empty)

            // Grade the video is for: null = all grades
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            $table->string('video_url', 1000); // YouTube link
            $table->unsignedInteger('duration_seconds')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_featured')->default(false); // shown on the home page
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_videos');
    }
};
