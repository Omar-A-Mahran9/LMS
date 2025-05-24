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

            // Contact fields
            $table->string('phone')->unique();
            $table->string('parent_phone')->nullable();
            $table->string('email')->unique()->nullable();

            // Additional info
            $table->string('parent_job')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->unsignedBigInteger('governorate_id')->nullable(); // "المحافظة"
            $table->unsignedBigInteger('category_id')->nullable();

            // Auth fields
             $table->string('password')->nullable();
            $table->boolean('block_flag')->default(false);
            $table->rememberToken();

            // Image
            $table->string('image')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
