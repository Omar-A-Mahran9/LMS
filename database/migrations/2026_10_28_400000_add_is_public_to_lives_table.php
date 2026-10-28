<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lives', function (Blueprint $table) {
            // Public lives are watched by everyone (guests too) on their own page
            $table->boolean('is_public')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('lives', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
