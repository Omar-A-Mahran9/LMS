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
 Schema::create('class_access_codes', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('class_id');
    $table->string('code')->unique();
    $table->boolean('single_use')->default(true); // true = مرة واحدة
    $table->unsignedInteger('usage_limit')->nullable(); // null = غير محدود
    $table->unsignedInteger('used_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->foreign('class_id')->references('id')->on('course_classes')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_access_codes');
    }
};
