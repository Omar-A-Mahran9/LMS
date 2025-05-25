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
Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // Basic multilingual fields
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            // Notes
            $table->text('note_en')->nullable();
            $table->text('note_ar')->nullable();

            // Media
            $table->string('image')->nullable(); // Thumbnail
            $table->string('slide_image')->nullable(); // Cover/slider image
            $table->string('video_url')->nullable(); // Promo/intro video

            // SEO
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Course details
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('have_discount')->default(false);
            $table->integer('discount_percentage');

            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('certificate_available')->default(false);

            // Timing and availability
            $table->unsignedInteger('duration_minutes')->nullable(); // total course time
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Enrollment control
            $table->integer('max_students')->nullable(); // null = unlimited
            $table->boolean('is_enrollment_open')->default(true);
            $table->integer('enrolled_students')->default(0); // for analytics

            // Zoom/live session details
            $table->string('zoom_link')->nullable();
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_password')->nullable();

            // Relationships
            $table->foreignId('instructor_id')->nullable()->constrained('admins')->nullOnDelete();
             $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();

            // Control flags
            $table->boolean('show_in_home')->default(false);
            $table->boolean('featured')->default(false);

            // Tracking
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);

            $table->timestamps();
            $table->softDeletes(); // Optional: for recoverable deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
