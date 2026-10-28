<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * requires_login = false: a guest opens the bundle's classes with the code alone (no account).
     * Guest activations are logged in bundle_student with no student
     * (NULLs don't collide in the unique bundle/student index, so many guests fit).
     */
    public function up(): void
    {
        Schema::table('bundles', function (Blueprint $table) {
            if (!Schema::hasColumn('bundles', 'requires_login')) {
                $table->boolean('requires_login')->default(true)->after('is_active');
            }
        });

        // Older databases: student_id was made nullable in the create migration after it had run.
        // No doctrine/dbal for ->change(): MySQL keeps the foreign key on a nullability change
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bundle_student MODIFY student_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn('requires_login');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('bundle_student')->whereNull('student_id')->delete();
            DB::statement('ALTER TABLE bundle_student MODIFY student_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
