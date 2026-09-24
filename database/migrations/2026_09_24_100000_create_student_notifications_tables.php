<?php

use App\Models\Ability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per notification (not per student), so a message to 2000 students is still one row.
        Schema::create('student_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('message');   // new_class | new_course | message
            $table->string('target', 20)->default('all');     // all | categories | students
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('body_ar')->nullable();
            $table->text('body_en')->nullable();
            $table->string('link')->nullable();               // website path, e.g. /courses/5
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'course_id']);
            $table->index(['type', 'class_id']);
        });

        Schema::create('student_notification_category', function (Blueprint $table) {
            $table->foreignId('student_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['student_notification_id', 'category_id'], 'sn_category_primary');
        });

        Schema::create('student_notification_student', function (Blueprint $table) {
            $table->foreignId('student_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->primary(['student_notification_id', 'student_id'], 'sn_student_primary');
        });

        Schema::create('student_notification_reads', function (Blueprint $table) {
            $table->foreignId('student_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->primary(['student_notification_id', 'student_id'], 'sn_reads_primary');
        });

        // Dashboard permissions for the new "notifications" module (same shape RoleSeeder creates),
        // given to the super admin role so the page shows up right away.
        foreach (['view', 'create', 'delete'] as $action) {
            $ability = Ability::firstOrCreate(
                ['name' => $action . '_notifications'],
                ['category' => 'notifications', 'action' => $action]
            );

            if (DB::table('roles')->where('id', 1)->exists()) {
                DB::table('ability_role')->insertOrIgnore(['ability_id' => $ability->id, 'role_id' => 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notification_reads');
        Schema::dropIfExists('student_notification_student');
        Schema::dropIfExists('student_notification_category');
        Schema::dropIfExists('student_notifications');

        $ids = Ability::where('category', 'notifications')->pluck('id');
        DB::table('ability_role')->whereIn('ability_id', $ids)->delete();
        Ability::whereIn('id', $ids)->delete();
    }
};
