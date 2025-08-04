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
                $table->string('code')->unique(); // الكود نفسه
                $table->boolean('single_use')->default(true); // true = مرة واحدة فقط
                $table->unsignedInteger('usage_limit')->nullable(); // null = غير محدود
                $table->unsignedInteger('used_count')->default(0); // كم مرة تم استخدامه
                $table->boolean('is_active')->default(true); // الكود مفعل؟
                $table->timestamps();

                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
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
