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
        Schema::create('classes', function (Blueprint $table) {
           $table->id();
            $table->string('image')->nullable(); // Thumbnail

            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Multilingual content
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            // Video info
            $table->string('video_url'); // Or use a file upload path
            $table->string('attachment')->nullable();

            // Additional fields
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
