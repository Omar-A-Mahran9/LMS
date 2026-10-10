<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseVideo;
use App\Models\Government;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Never let these tests run against a real database
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            $this->fail('Feature tests must run on the in-memory sqlite database (see phpunit.xml).');
        }
    }

    protected function makeCategory(): Category
    {
        return Category::create([
            'name_ar' => 'الصف ' . uniqid(),
            'name_en' => 'Grade ' . uniqid(),
            'description_ar' => 'وصف',
            'description_en' => 'desc',
        ]);
    }

    protected function makeStudent(array $attributes = []): Student
    {
        $government = Government::first() ?? Government::create(['name_ar' => 'القاهرة', 'name_en' => 'Cairo']);

        return Student::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'phone' => (string) random_int(10000000000, 19999999999),
            'email' => uniqid('student') . '@example.test',
            'password' => 'secret123',
            'gender' => 'male',
            'government_id' => $government->id,
            'category_id' => $this->makeCategory()->id,
            'block_flag' => 0,
        ], $attributes));
    }

    protected function actingAsStudent(Student $student): Student
    {
        Sanctum::actingAs($student, ['*'], 'api');

        return $student;
    }

    protected function makeCourse(array $attributes = []): Course
    {
        return Course::create(array_merge([
            'title_en' => 'Course',
            'title_ar' => 'كورس',
            'slug' => uniqid('course-'),
            'is_active' => 1,
            'is_enrollment_open' => 1,
            'is_class' => 1,
            'category_id' => $this->makeCategory()->id,
        ], $attributes));
    }

    protected function enroll(Student $student, Course $course, string $status = 'approved'): void
    {
        DB::table('course_student')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'payment_type' => 'pay_in_center',
            'status' => $status,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeClass(Course $course, array $attributes = []): CourseClass
    {
        return CourseClass::create(array_merge([
            'course_id' => $course->id,
            'title_en' => 'Class',
            'title_ar' => 'حصة',
            'is_active' => 1,
        ], $attributes));
    }

    protected function makeVideo(CourseClass $class, array $attributes = []): CourseVideo
    {
        return CourseVideo::create(array_merge([
            'course_id' => $class->course_id,
            'class_id' => $class->id,
            'title_en' => 'Video',
            'title_ar' => 'فيديو',
            'video_url' => 'https://www.youtube.com/watch?v=SECRETVIDEO1',
            'duration_seconds' => 100,
            'is_active' => 1,
        ], $attributes));
    }

    /**
     * A quiz with one multiple choice question (answer "right" is correct) and one short answer question.
     */
    protected function makeQuiz(Course $course, ?CourseClass $class = null, array $attributes = []): Quiz
    {
        $quiz = Quiz::create(array_merge([
            'course_id' => $course->id,
            'class_id' => $class?->id,
            'title_en' => 'Quiz',
            'title_ar' => 'اختبار',
            'is_active' => 1,
        ], $attributes));

        $mcq = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_en' => 'Pick the right one',
            'question_ar' => 'اختر',
            'type' => 'multiple_choice',
            'points' => 1,
        ]);
        QuizAnswer::create(['quiz_question_id' => $mcq->id, 'answer_en' => 'right', 'answer_ar' => 'صح', 'is_correct' => 1]);
        QuizAnswer::create(['quiz_question_id' => $mcq->id, 'answer_en' => 'wrong', 'answer_ar' => 'خطأ', 'is_correct' => 0]);

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_en' => 'Type the secret',
            'question_ar' => 'اكتب',
            'type' => 'short_answer',
            'expected_answer' => 'photosynthesis',
            'points' => 1,
        ]);

        return $quiz->fresh();
    }
}
