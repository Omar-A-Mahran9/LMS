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
        Schema::create('customer_rates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('image')->nullable();
            $table->integer('rate')->default(5); // Default rating
             $table->enum('status', ['approve', 'reject', 'pending'])->default('pending'); // Review status
            $table->string('audio')->nullable(); // Optional audio feedback
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_rates');
    }
};
