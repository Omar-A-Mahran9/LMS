<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bundle_class', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('bundle_id');

            $table->unsignedBigInteger('class_id');


            $table->foreign('bundle_id')
                ->references('id')
                ->on('bundles')
                ->onDelete('cascade');

            $table->foreign('class_id')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};
