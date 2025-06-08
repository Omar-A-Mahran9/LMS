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
       Schema::create('course_video_student', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_video_id')->constrained()->onDelete('cascade');
            $table->integer('last_watched_second')->default(0); // Seek position

            $table->boolean('is_completed')->default(false);
            $table->integer('watch_seconds')->default(0); // total watch time
            $table->timestamp('last_watched_at')->nullable(); // when they last watched
             $table->timestamp('completed_at')->nullable(); // when they last watched

            $table->integer('views')->default(0); // number of times they played it

            $table->timestamps();

            $table->unique(['student_id', 'course_video_id']); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_video_student');
    }
};
