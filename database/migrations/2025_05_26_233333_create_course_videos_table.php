<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_videos', function (Blueprint $table) {
           $table->id();
            $table->string('image')->nullable(); // Thumbnail

            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');

            $table->foreignId('course_section_id')->nullable()->constrained()->onDelete('cascade');

            // Multilingual content
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            // Video info
            $table->string('video_url'); // Or use a file upload path
            $table->integer('duration_seconds')->nullable(); // optional for tracking

            // Additional fields
            $table->unsignedInteger('order')->default(0); // sort videos
            $table->boolean('is_preview')->default(false); // free preview toggle
            $table->boolean('is_active')->default(true);
            $table->boolean('quiz_required')->default(false);

            $table->integer('views')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_videos');
    }
};
