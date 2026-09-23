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
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            // All options the student picked for a multiple choice question that has more than one correct answer
            $table->json('selected_answer_ids')->nullable()->after('quiz_answer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropColumn('selected_answer_ids');
        });
    }
};
