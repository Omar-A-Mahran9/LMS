<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Name fields
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('login_code')->unique()->nullable();

            // Contact fields
            $table->string('phone')->unique();
            $table->string('parent_phone')->nullable();
            $table->string('email')->unique()->nullable();

            // Additional info
            $table->string('parent_job')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->unsignedBigInteger('government_id')->nullable(); // "المحافظة"
            $table->unsignedBigInteger('category_id')->nullable();

            // Auth fields
            $table->string('password')->nullable();
            $table->boolean('block_flag')->default(false);
            $table->rememberToken();

            // Image
            $table->string('image')->nullable();

            // OTP fields
            $table->string('otp')->nullable();
            $table->timestamp('otp_expiration')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('government_id')->references('id')->on('governments')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
