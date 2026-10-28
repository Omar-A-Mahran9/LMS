<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sliders are ordered by hand (drag and drop in the dashboard), smallest first.
     */
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });

        // Keep the order they were shown in until now: newest first
        DB::table('sliders')->orderByDesc('created_at')->orderByDesc('id')->pluck('id')
            ->each(fn ($id, $i) => DB::table('sliders')->where('id', $id)->update(['sort_order' => $i + 1]));
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
