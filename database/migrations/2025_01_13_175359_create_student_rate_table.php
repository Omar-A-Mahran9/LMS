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
        Schema::create('student_rate', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('image')->nullable();
            $table->integer('rate')->default(5); // Default rating
            $table->enum('status', ['approve', 'reject', 'pending'])->default('pending'); // Review status
            $table->string('audio')->nullable(); // Optional audio feedback
            $table->text('text')->nullable(); // ✅ New text field for written feedback

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            // Foreign key constraint.
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_rate');
    }
};
