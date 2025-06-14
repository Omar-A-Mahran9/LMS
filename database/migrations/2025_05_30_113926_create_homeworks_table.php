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
        Schema::create('home_works', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('course_section_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            $table->integer('duration_minutes')->nullable(); // Time limit
            $table->boolean('is_active')->default(true);
            $table->integer('attempt')->default(0);

            $table->timestamps();
        });

        Schema::create('home_work_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_work_id')->constrained('home_works')->onDelete('cascade');

            $table->text('question_en');
            $table->text('question_ar');
            $table->text('expected_answer')->nullable();

            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer'])->default('multiple_choice');

            $table->integer('points')->default(1);
            $table->timestamps();
        });

        Schema::create('home_work_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_work_question_id')->constrained('home_work_questions')->onDelete('cascade');

            $table->text('answer_en');
            $table->text('answer_ar');
            $table->boolean('is_correct')->default(false);

            $table->timestamps();
        });

        Schema::create('home_work_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_work_id')->constrained('home_works')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('score')->nullable();

            $table->timestamps();
        });

        Schema::create('home_work_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_work_attempt_id')->constrained('home_work_attempts')->onDelete('cascade');
            $table->foreignId('home_work_question_id')->constrained('home_work_questions')->onDelete('cascade');

            $table->text('answer_text')->nullable(); // for short answers
            $table->foreignId('home_work_answer_id')->nullable()->constrained()->onDelete('set null'); // for MCQ

            $table->timestamps();
        });




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_works');
    }
};
