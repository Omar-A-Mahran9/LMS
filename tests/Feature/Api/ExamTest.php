<?php

namespace Tests\Feature\Api;

use App\Models\HomeWork;
use App\Models\HomeWorkAnswer;
use App\Models\HomeWorkAttempt;
use App\Models\HomeWorkQuestion;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;

class ExamTest extends ApiTestCase
{
    private function enrolledStudentWithQuiz(array $quizAttributes = []): array
    {
        $course = $this->makeCourse();
        $class = $this->makeClass($course);
        $quiz = $this->makeQuiz($course, $class, $quizAttributes);
        $student = $this->actingAsStudent($this->makeStudent());
        $this->enroll($student, $course);

        return [$student, $quiz, $course];
    }

    private function answersFor(Quiz $quiz, bool $correct): array
    {
        $mcq = $quiz->questions->firstWhere('type', 'multiple_choice');
        $short = $quiz->questions->firstWhere('type', 'short_answer');

        return [
            ['id' => $mcq->id, 'answer' => $mcq->answers->firstWhere('is_correct', $correct ? 1 : 0)->id],
            ['id' => $short->id, 'answer' => $correct ? 'Photosynthesis' : 'no idea'],
        ];
    }

    private function makeHomework($course): HomeWork
    {
        $homework = HomeWork::create([
            'course_id' => $course->id,
            'title_en' => 'HW',
            'title_ar' => 'واجب',
            'is_active' => 1,
            'attempt_count' => 1,
        ]);
        $q = HomeWorkQuestion::create([
            'home_work_id' => $homework->id,
            'question_en' => 'Pick',
            'question_ar' => 'اختر',
            'type' => 'multiple_choice',
            'expected_answer' => null,
            'points' => 2,
        ]);
        HomeWorkAnswer::create(['home_work_question_id' => $q->id, 'answer_en' => 'yes', 'answer_ar' => 'نعم', 'is_correct' => 1]);
        HomeWorkAnswer::create(['home_work_question_id' => $q->id, 'answer_en' => 'no', 'answer_ar' => 'لا', 'is_correct' => 0]);
        HomeWorkQuestion::create([
            'home_work_id' => $homework->id,
            'question_en' => 'Type',
            'question_ar' => 'اكتب',
            'type' => 'short_answer',
            'expected_answer' => 'mitochondria',
            'points' => 1,
        ]);

        return $homework->fresh();
    }

    public function test_starting_a_quiz_does_not_reveal_the_answers(): void
    {
        [, $quiz] = $this->enrolledStudentWithQuiz();

        $response = $this->getJson("/api/quizzes/{$quiz->id}/start")->assertOk();

        $this->assertStringNotContainsString('photosynthesis', $response->getContent());
        $this->assertStringNotContainsString('is_correct', $response->getContent());
        $this->assertNotNull($response->json('data.attempt.attempt_id'));
    }

    public function test_full_quiz_flow_scores_on_the_server(): void
    {
        [$student, $quiz] = $this->enrolledStudentWithQuiz();
        $attemptId = $this->getJson("/api/quizzes/{$quiz->id}/start")->json('data.attempt.attempt_id');

        // Autosave, then a refresh resumes the same attempt with the saved answers
        $this->postJson("/api/student-quizzes/{$attemptId}/save", ['answers' => [$this->answersFor($quiz, true)[0]]])->assertOk();
        $resumed = $this->getJson("/api/quizzes/{$quiz->id}/start")->assertOk();
        $this->assertSame($attemptId, $resumed->json('data.attempt.attempt_id'));
        $this->assertCount(1, $resumed->json('data.attempt.saved_answers'));

        // A client-sent "score" is ignored
        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => $this->answersFor($quiz, true), 'score' => 999])
            ->assertOk()
            ->assertJsonPath('data.score', 2)
            ->assertJsonPath('data.total_points', 2);

        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => $this->answersFor($quiz, true)])->assertStatus(400);

        $this->getJson("/api/student-quizzes/{$attemptId}/results")
            ->assertOk()
            ->assertJsonPath('data.score', 2)
            ->assertJsonPath('data.full_score', 2);
    }

    public function test_quiz_cannot_be_submitted_with_too_few_answers(): void
    {
        [, $quiz] = $this->enrolledStudentWithQuiz();
        $attemptId = $this->getJson("/api/quizzes/{$quiz->id}/start")->json('data.attempt.attempt_id');

        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => [$this->answersFor($quiz, true)[0]]])
            ->assertStatus(422);
        $this->assertNull(QuizAttempt::find($attemptId)->submitted_at);
    }

    public function test_attempt_limit_is_enforced(): void
    {
        [, $quiz] = $this->enrolledStudentWithQuiz(['attempt_count' => 1]);
        $attemptId = $this->getJson("/api/quizzes/{$quiz->id}/start")->json('data.attempt.attempt_id');
        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => $this->answersFor($quiz, false)])->assertOk();

        $this->getJson("/api/quizzes/{$quiz->id}/start")->assertStatus(400);
    }

    public function test_students_cannot_touch_each_others_quiz_attempts(): void
    {
        [, $quiz, $course] = $this->enrolledStudentWithQuiz();
        $attemptId = $this->getJson("/api/quizzes/{$quiz->id}/start")->json('data.attempt.attempt_id');

        $intruder = $this->actingAsStudent($this->makeStudent());
        $this->enroll($intruder, $course);

        $this->postJson("/api/student-quizzes/{$attemptId}/save", ['answers' => $this->answersFor($quiz, true)])->assertStatus(400);
        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => $this->answersFor($quiz, true)])->assertStatus(400);
        $this->getJson("/api/student-quizzes/{$attemptId}/results")->assertStatus(400);
    }

    public function test_guest_asking_for_missing_quiz_results_gets_a_clean_error(): void
    {
        $this->getJson('/api/student-quizzes/999999/results')->assertStatus(400);
    }

    public function test_arabic_short_answers_can_be_graded_correct(): void
    {
        [, $quiz] = $this->enrolledStudentWithQuiz();
        QuizQuestion::where('quiz_id', $quiz->id)->where('type', 'short_answer')->update(['expected_answer' => 'البناء الضوئي']);
        $quiz = $quiz->fresh();
        $attemptId = $this->getJson("/api/quizzes/{$quiz->id}/start")->json('data.attempt.attempt_id');

        $answers = $this->answersFor($quiz, false);
        $answers[1]['answer'] = 'البناء الضوئي';
        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => $answers])
            ->assertOk()
            ->assertJsonPath('data.score', 1);

        $this->getJson("/api/student-quizzes/{$attemptId}/results")->assertJsonPath('data.score', 1);
    }

    public function test_wrong_arabic_short_answer_is_not_graded_correct(): void
    {
        [, $quiz] = $this->enrolledStudentWithQuiz();
        QuizQuestion::where('quiz_id', $quiz->id)->where('type', 'short_answer')->update(['expected_answer' => 'البناء الضوئي']);
        $quiz = $quiz->fresh();
        $attemptId = $this->getJson("/api/quizzes/{$quiz->id}/start")->json('data.attempt.attempt_id');

        $answers = $this->answersFor($quiz, false);
        $answers[1]['answer'] = 'التنفس الخلوي';
        $this->postJson("/api/student-quizzes/{$attemptId}/submit", ['answers' => $answers])
            ->assertOk()
            ->assertJsonPath('data.score', 0);
    }

    public function test_starting_homework_does_not_reveal_the_answers(): void
    {
        [, , $course] = $this->enrolledStudentWithQuiz();
        $homework = $this->makeHomework($course);

        $response = $this->getJson("/api/homeworks/{$homework->id}/start")->assertOk();
        $this->assertStringNotContainsString('mitochondria', $response->getContent());
    }

    public function test_homework_flow_and_no_resubmission(): void
    {
        [, , $course] = $this->enrolledStudentWithQuiz();
        $homework = $this->makeHomework($course);
        $mcq = $homework->questions->firstWhere('type', 'multiple_choice');
        $short = $homework->questions->firstWhere('type', 'short_answer');
        $attemptId = $this->getJson("/api/homeworks/{$homework->id}/start")->json('data.attempt.attempt_id');

        $wrong = [['id' => $mcq->id, 'answer' => $mcq->answers->firstWhere('is_correct', 0)->id], ['id' => $short->id, 'answer' => 'x']];
        $right = [['id' => $mcq->id, 'answer' => $mcq->answers->firstWhere('is_correct', 1)->id], ['id' => $short->id, 'answer' => 'mitochondria']];

        $this->postJson("/api/student-homeworks/{$attemptId}/submit", ['answers' => $wrong])->assertOk()->assertJsonPath('data.score', 0);

        // Re-submitting the same attempt used to overwrite the score and duplicate the answers
        $this->postJson("/api/student-homeworks/{$attemptId}/submit", ['answers' => $right])->assertStatus(400);
        $this->assertSame(0, (int) HomeWorkAttempt::find($attemptId)->score);
        $this->assertSame(2, HomeWorkAttempt::find($attemptId)->answers()->count());

        $this->getJson("/api/student-homeworks/{$attemptId}/results")->assertOk()->assertJsonPath('data.score', 0);
    }

    public function test_homework_answer_from_another_question_scores_nothing(): void
    {
        [, , $course] = $this->enrolledStudentWithQuiz();
        $homework = $this->makeHomework($course);
        $mcq = $homework->questions->firstWhere('type', 'multiple_choice');
        $short = $homework->questions->firstWhere('type', 'short_answer');
        $attemptId = $this->getJson("/api/homeworks/{$homework->id}/start")->json('data.attempt.attempt_id');

        $this->postJson("/api/student-homeworks/{$attemptId}/submit", ['answers' => [
            ['id' => $mcq->id, 'answer' => 999999],
            ['id' => $short->id, 'answer' => 'x'],
        ]])->assertOk()->assertJsonPath('data.score', 0);
    }

    public function test_students_cannot_submit_each_others_homework(): void
    {
        [$owner, , $course] = $this->enrolledStudentWithQuiz();
        $homework = $this->makeHomework($course);
        $attemptId = $this->getJson("/api/homeworks/{$homework->id}/start")->json('data.attempt.attempt_id');
        $mcq = $homework->questions->firstWhere('type', 'multiple_choice');
        $short = $homework->questions->firstWhere('type', 'short_answer');

        $intruder = $this->actingAsStudent($this->makeStudent());
        $this->enroll($intruder, $course);

        $this->postJson("/api/student-homeworks/{$attemptId}/submit", ['answers' => [
            ['id' => $mcq->id, 'answer' => $mcq->answers->firstWhere('is_correct', 0)->id],
            ['id' => $short->id, 'answer' => 'x'],
        ]])->assertStatus(404);

        $this->assertNull(HomeWorkAttempt::find($attemptId)->submitted_at);
        $this->getJson("/api/student-homeworks/{$attemptId}/results")->assertStatus(400);
    }
}
