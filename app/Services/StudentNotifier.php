<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\StudentNotification;

class StudentNotifier
{
    /**
     * New class (حصة): goes to the students of the class's grade (the course's category and
     * sub-categories). A class in a course without a grade goes to everyone.
     */
    public static function newClass(CourseClass $class): ?StudentNotification
    {
        if (!$class->is_active || self::alreadySent(StudentNotification::TYPE_NEW_CLASS, 'class_id', $class->id)) {
            return null;
        }

        $course = $class->course;
        $categoryIds = $course ? self::courseCategoryIds($course) : [];

        return self::send([
            'type'     => StudentNotification::TYPE_NEW_CLASS,
            'title_ar' => 'حصة جديدة: ' . $class->title_ar,
            'title_en' => 'New class: ' . $class->title_en,
            'body_ar'  => $course ? 'نزلت حصة جديدة في ' . $course->title_ar . '، ادخل شوفها دلوقتي.' : 'نزلت حصة جديدة، ادخل شوفها دلوقتي.',
            'body_en'  => $course ? 'A new class was added to ' . $course->title_en . '.' : 'A new class was added.',
            'link'     => $course ? self::courseLink($course) : null,
            'course_id' => $course?->id,
            'class_id' => $class->id,
        ], $categoryIds ? StudentNotification::TARGET_CATEGORIES : StudentNotification::TARGET_ALL, $categoryIds);
    }

    /** New course: announced to all students. */
    public static function newCourse(Course $course): ?StudentNotification
    {
        if (!$course->is_active || self::alreadySent(StudentNotification::TYPE_NEW_COURSE, 'course_id', $course->id)) {
            return null;
        }

        return self::send([
            'type'      => StudentNotification::TYPE_NEW_COURSE,
            'title_ar'  => 'كورس جديد: ' . $course->title_ar,
            'title_en'  => 'New course: ' . $course->title_en,
            'body_ar'   => 'نزل كورس جديد على المنصة، ادخل اعرف تفاصيله.',
            'body_en'   => 'A new course is now available on the platform.',
            'link'      => self::courseLink($course),
            'course_id' => $course->id,
        ], StudentNotification::TARGET_ALL);
    }

    /** The student's subscription to a course was activated from the dashboard: goes to that student only. */
    public static function subscriptionActivated(Enrollment $enrollment): ?StudentNotification
    {
        $course = $enrollment->course()->withoutGlobalScopes()->first();
        if (!$course || !$enrollment->student_id) {
            return null;
        }

        // Clicking approve / activate a few times in a row should still send one notification
        $recentlySent = StudentNotification::where('type', StudentNotification::TYPE_SUBSCRIPTION)
            ->where('course_id', $course->id)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->whereHas('students', fn ($q) => $q->where('students.id', $enrollment->student_id))
            ->exists();
        if ($recentlySent) {
            return null;
        }

        return self::send([
            'type'      => StudentNotification::TYPE_SUBSCRIPTION,
            'title_ar'  => 'تم تفعيل اشتراكك في ' . $course->title_ar,
            'title_en'  => 'Your subscription to ' . $course->title_en . ' is active',
            'body_ar'   => 'اشتراكك اتفعّل، تقدر تبدأ تتفرج على الحصص وتحل الامتحانات دلوقتي.',
            'body_en'   => 'Your subscription is active. You can start watching the classes now.',
            'link'      => self::courseLink($course),
            'course_id' => $course->id,
        ], StudentNotification::TARGET_STUDENTS, [$enrollment->student_id]);
    }

    /**
     * @param string $target  all | categories | students
     * @param array  $ids     category ids or student ids depending on the target
     */
    public static function send(array $data, string $target = StudentNotification::TARGET_ALL, array $ids = []): StudentNotification
    {
        $notification = StudentNotification::create($data + ['target' => $target]);

        if ($target === StudentNotification::TARGET_CATEGORIES) {
            $notification->categories()->sync($ids);
        } elseif ($target === StudentNotification::TARGET_STUDENTS) {
            $notification->students()->sync($ids);
        }

        return $notification;
    }

    /** Website page for a course (same routes the site already uses). */
    public static function courseLink(Course $course): string
    {
        return $course->is_class ? "/grade/class/{$course->id}" : "/courses/{$course->id}";
    }

    private static function courseCategoryIds(Course $course): array
    {
        return collect([$course->category_id])
            ->merge($course->subCategories()->withoutGlobalScopes()->pluck('categories.id'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function alreadySent(string $type, string $column, int $id): bool
    {
        return StudentNotification::where('type', $type)->where($column, $id)->exists();
    }
}
