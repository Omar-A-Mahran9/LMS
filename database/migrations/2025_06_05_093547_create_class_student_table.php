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
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
             $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
             $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');

            $table->boolean('is_active')->default(false);
                $table->timestamps();

        // تأكد من عدم تكرار نفس الطالب في نفس الصف
        $table->unique(['student_id', 'class_id']);        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_student_tabl');
    }
};
