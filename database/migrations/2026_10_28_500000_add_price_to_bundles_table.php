<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The price columns were added to the create migration after it had already run,
        // so older databases miss them (fresh ones already have them)
        Schema::table('bundles', function (Blueprint $table) {
            if (!Schema::hasColumn('bundles', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('bundles', 'discount_price')) {
                $table->decimal('discount_price', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Nothing to undo: on fresh databases these columns belong to the create migration
    }
};
