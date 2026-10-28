<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bundles the students activated with a code: the student keeps the bundle's classes
     * (the code is used up once, at activation, not on every class opened).
     */
    public function up(): void
    {
        Schema::create('bundle_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('bundles')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('access_code_id')->nullable()->constrained('bundle_access_codes')->nullOnDelete();
            $table->string('device_ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('used_at')->nullable(); // activation time
            $table->timestamps();

            $table->unique(['bundle_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_student');
    }
};
