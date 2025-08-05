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
            Schema::create('class_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable(); // ممكن null لو المستخدم مش مسجل
            $table->unsignedBigInteger('class_id');
            $table->string('access_code'); // الكود المستخدم
            $table->ipAddress('device_ip')->nullable(); // IP
            $table->string('user_agent')->nullable(); // بيانات الجهاز/المتصفح
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->foreign('access_code_id')->references('id')->on('class_access_codes')->onDelete('cascade');

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_access_logs');
    }
};
