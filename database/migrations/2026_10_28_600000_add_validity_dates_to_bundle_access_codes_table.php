<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The validity window was added to the create migration after it had already run,
        // so older databases miss it (fresh ones already have it)
        Schema::table('bundle_access_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('bundle_access_codes', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }

            if (!Schema::hasColumn('bundle_access_codes', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Nothing to undo: on fresh databases these columns belong to the create migration
    }
};
