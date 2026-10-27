<?php

use App\Models\Ability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Banners / offers shown on the website, fully managed from the dashboard.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Where it shows: top_bar | popup | home_top | home_middle | home_bottom | courses_page
            $table->string('placement', 30)->index();

            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('subtitle_ar', 500)->nullable();
            $table->string('subtitle_en', 500)->nullable();
            $table->string('badge_ar', 60)->nullable();   // e.g. "خصم 30%"
            $table->string('badge_en', 60)->nullable();

            $table->string('image')->nullable();          // desktop image
            $table->string('mobile_image')->nullable();   // falls back to the desktop image

            $table->string('btn_title_ar')->nullable();
            $table->string('btn_title_en')->nullable();
            $table->string('link', 1000)->nullable();     // "/courses/5" or a full URL
            $table->boolean('open_in_new_tab')->default(false);

            $table->string('bg_color', 20)->nullable();   // top bar / text-only banners
            $table->string('text_color', 20)->nullable();

            // Who sees it: all | guests | students, optionally only one grade
            $table->string('audience', 20)->default('all');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Cairo wall-clock time, like lives (null = no limit)
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('show_countdown')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'placement']);
        });

        // Same as notifications: create the abilities and give them to the super admin role
        // so the page shows up right away without re-running the seeder.
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $ability = Ability::firstOrCreate(
                ['name' => $action . '_banners'],
                ['category' => 'banners', 'action' => $action]
            );

            if (DB::table('roles')->where('id', 1)->exists()) {
                DB::table('ability_role')->insertOrIgnore(['ability_id' => $ability->id, 'role_id' => 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');

        $ids = Ability::where('category', 'banners')->pluck('id');
        DB::table('ability_role')->whereIn('ability_id', $ids)->delete();
        Ability::whereIn('id', $ids)->delete();
    }
};
