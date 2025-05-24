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
        Schema::create('admins', function (Blueprint $table) {
          $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('phone')->unique()->nullable();
        $table->string('password');

        // Optional profile fields
        $table->string('image')->nullable(); // Profile image
        $table->string('title')->nullable(); // e.g., Professor, Instructor
        $table->text('bio')->nullable(); // Short biography
        $table->string('specialization')->nullable(); // Area of expertise
        $table->string('linkedin')->nullable();
        $table->string('website')->nullable();

        // LMS-specific fields
        $table->unsignedInteger('experience_years')->default(0);
        $table->boolean('is_active')->default(true); // Can teach?
        $table->enum('type', ['admin', 'instructor'])->default('admin');

         $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
