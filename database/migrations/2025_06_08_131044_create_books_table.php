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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
             $table->string('image')->nullable(); // Thumbnail
            // Multilingual content
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('have_discount')->default(false);
            $table->integer('discount_percentage')->nullable();

            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(true);

            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
