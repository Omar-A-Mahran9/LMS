<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unsignedInteger('answered_count')->default(0)->after('score');
            $table->decimal('answered_percent', 5, 2)->default(0)->after('answered_count');
        });

        // Before this change a submit was only accepted with every question answered,
        // so already-submitted attempts are complete and must keep the class unlocked.
        DB::table('quiz_attempts')
            ->whereNotNull('submitted_at')
            ->update([
                'answered_percent' => 100,
                'answered_count' => DB::raw('(select count(*) from quiz_questions where quiz_questions.quiz_id = quiz_attempts.quiz_id)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['answered_count', 'answered_percent']);
        });
    }
};
