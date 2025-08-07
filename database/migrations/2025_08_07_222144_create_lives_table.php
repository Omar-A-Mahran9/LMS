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
        Schema::create('lives', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();

            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            $table->enum('platform', ['zoom', 'youtube', 'twitch'])->default('zoom');
            $table->string('embed_url');
            $table->string('stream_key')->nullable();
            $table->string('meeting_id')->nullable();
            $table->string('password')->nullable();

            $table->dateTime('start_time');
            $table->integer('duration_minutes')->nullable();

            $table->boolean('chat_enabled')->default(true);
            $table->boolean('is_recorded')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('chat_embed_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lives');
    }
};
