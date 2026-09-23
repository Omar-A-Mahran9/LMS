<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QuizResource;
use App\Models\ClassAccessCode;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;

class StudentQuizController extends Controller
{
    // Start a quiz attempt or get existing attempt
public function startQuiz(Request $request, $quizId)
{
    $code=$request->code;
     $studentId = auth()->id();
     $quiz = Quiz::with('course')->find($quizId);

    if (!$quiz) {
        return $this->failure(__('Quiz is not found'));
    }

    if (!$quiz->course || !$quiz->course->is_active) {
        return $this->failure('Quiz is not linked to an active course.');
    }
if ($code) {
    // Get all class_ids related to this course
    $classIds = $quiz->course->classes->pluck('id');

    $accessCode = ClassAccessCode::whereIn('class_id', $classIds)
        ->where('code', $code)
        ->where('is_active', true)
        ->first();
     if (!$accessCode) {
        return $this->failure(__('Invalid or inactive access code.'));
    }

    // Check single_use
    if ($accessCode->single_use && $accessCode->used_count >= 1) {
        return $this->failure(__('This code has already been used.'));
    }

    // Check usage_limit
    if ($accessCode->usage_limit !== null && $accessCode->used_count >= $accessCode->usage_limit) {
        return $this->failure(__('This code has reached its usage limit.'));
    }

}

  if ($studentId !== null && !$quiz->course->isStudentEnrolled($studentId)) {
    return $this->failure('You are not enrolled in this course.');
}

   if ($quiz->questions->isEmpty()) {
        return $this->failure(__('Quiz does not contain any questions.'));
    }

    // Resume an unfinished attempt instead of opening a new one (time-expired ones get graded here)
    $attempt = null;
    if ($studentId !== null) {
        $attempt = $quiz->accessStatusFor($studentId)['open_attempt'];
    }

    if (!$attempt) {
        if ($studentId !== null && $quiz->attempt_count !== null) {
            $usedAttempts = QuizAttempt::where('quiz_id', $quizId)
                ->where('student_id', $studentId)
                ->count();

            if ($usedAttempts >= $quiz->attempt_count) {
                return $this->failure(__('You have reached the maximum number of attempts.'));
            }
        }

        $quiz->increment('attempt');

        $attempt = QuizAttempt::create([
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'code'=>$code,
            'started_at' => now(),
        ]);
    }
    $attempt->setRelation('quiz', $quiz);

    return $this->success('', [
        'attempt' => [
            'attempt_id' => $attempt->id,
            'student_id' => $studentId,
            'started_at' => $attempt->started_at->format('H:i:s'),
            'remaining_seconds' => $attempt->remainingSeconds(),
            'answered_percent' => $attempt->answered_percent,
            'required_percent' => Quiz::REQUIRED_ANSWERED_PERCENT,
            // Previously saved answers so the student continues where they stopped
            'saved_answers' => $attempt->answers()->get()->map(fn ($a) => [
                'id' => $a->quiz_question_id,
                'answer' => $a->quiz_answer_id ?? $a->answer_text,
            ])->values(),
        ],
        'quiz' => new QuizResource($quiz),
    ]);
}


// Autosave the student's answers while solving, so leaving the page doesn't lose them
public function saveProgress(Request $request, $quizAttemptId)
{
    $attempt = $this->findOwnedAttempt($quizAttemptId);
    if (!$attempt) {
        return $this->failure(__('Quiz attempt not found or access denied.'));
    }

    if ($attempt->isSubmitted()) {
        return $this->failure(__('This quiz attempt has already been submitted.'));
    }

    if ($attempt->isExpired()) {
        $attempt->finalize();
        return $this->failure(__('The quiz time has expired.'));
    }

    $data = $request->validate([
        'answers' => 'required|array',
        'answers.*.id' => 'required|integer|exists:quiz_questions,id',
        'answers.*.answer' => 'nullable',
    ]);

    $attempt->storeAnswers(collect($data['answers'])->mapWithKeys(fn ($item) => [$item['id'] => $item['answer'] ?? null])->toArray());

    return $this->success('', [
        'answered_count' => $attempt->answered_count,
        'answered_percent' => $attempt->answered_percent,
        'required_percent' => Quiz::REQUIRED_ANSWERED_PERCENT,
    ]);
}


public function submitQuiz(Request $request, $quizAttemptId)
{
    $studentId = auth()->id();

    $attempt = $this->findOwnedAttempt($quizAttemptId);
    if (!$attempt) {
        return $this->failure(__('Quiz attempt not found or access denied.'));
    }

    if ($studentId !== null && !$attempt->quiz->course->isStudentEnrolled($attempt->student_id)) {
        return $this->failure('You are not enrolled in this course.');
    }

    if ($attempt->isSubmitted()) {
        return $this->failure(__('This quiz attempt has already been submitted.'));
    }

    $data = $request->validate([
        'answers' => 'nullable|array',
        'answers.*.id' => 'required|integer|exists:quiz_questions,id',
        'answers.*.answer' => 'nullable',
    ]);

    // Few seconds of tolerance both ways: the client timer auto-submits at 0:00 and may be slightly off
    $expired = $attempt->isExpired(-5);

    // Once the time is really over, nothing new is accepted: only what was autosaved gets graded
    if (!$attempt->isExpired(30) && !empty($data['answers'])) {
        $attempt->storeAnswers(collect($data['answers'])->mapWithKeys(fn ($item) => [$item['id'] => $item['answer'] ?? null])->toArray());
    } else {
        $attempt->refreshProgress();
    }

    if (!$expired && $attempt->answered_percent < Quiz::REQUIRED_ANSWERED_PERCENT) {
        return response()->json([
            'message' => __('You must answer at least :percent% of the questions before submitting.', ['percent' => Quiz::REQUIRED_ANSWERED_PERCENT]),
            'answered_percent' => $attempt->answered_percent,
            'required_percent' => Quiz::REQUIRED_ANSWERED_PERCENT,
        ], 422);
    }

    $attempt->finalize();

    $totalPoints = (int) $attempt->quiz->questions()->sum('points');

    return $this->success($expired ? __('The quiz time has expired.') : __('Quiz submitted successfully'), [
        'score' => $attempt->score,
        'course_id' => $attempt->quiz?->course?->id,
        'class_id' => $attempt->quiz?->class?->id,
        'total_points' => $totalPoints,
        'score_text' => "{$attempt->score}/{$totalPoints}",
        'answered_percent' => $attempt->answered_percent,
        'required_percent' => Quiz::REQUIRED_ANSWERED_PERCENT,
        // false => the class is still locked (e.g. time ran out below the required %)
        'class_unlocked' => $attempt->meetsRequiredPercent(),
    ]);
}

private function findOwnedAttempt($quizAttemptId): ?QuizAttempt
{
    $attempt = QuizAttempt::with('quiz')->find($quizAttemptId);
    if (!$attempt || $attempt->student_id != auth()->id()) {
        return null;
    }

    return $attempt;
}



private function checkAnswer($question, $studentAnswer)
    {
        switch ($question->type) {
            case 'true_false':
            case 'multiple_choice':
                $correctAnswers = $question->answers->where('is_correct', 1)->pluck('answer_en')->map(fn($a) => strtolower(trim($a)))->toArray();
                return in_array(strtolower(trim($studentAnswer)), $correctAnswers);

            case 'short_answer':
                return strtolower(trim($studentAnswer)) === strtolower(trim($question->expected_answer));

            default:
                return false;
        }
    }

public function results($studentQuizId)
{
$studentId = auth()->id();

 $attempt = QuizAttempt::with([
    'quiz.questions.answers',
    'answers.answer'
    ])->where('id', $studentQuizId)
    ->where('student_id', auth()->id())
    ->first();

    if ($studentId !== null && !$attempt) {
        return $this->failure('Quiz attempt not found or access denied.');
    }

    if ($studentId !== null && !$attempt->quiz->course->isStudentEnrolled($attempt->student_id)) {
            return $this->failure('You are not enrolled in this course.');
        }
        $results = [];
        $totalScore = 0;
        $fullScore = 0;

        foreach ($attempt->quiz->questions as $question) {
            $fullScore += $question->points;

            $attemptAnswer = $attempt->answers->firstWhere('quiz_question_id', $question->id);

            if ($question->type === 'short_answer') {
                // For short‑answer, single “correct” text
                $correctAnswers = [
                    'answer' => $question->expected_answer,
                ];
            } else {
                // For MCQ / true_false, collect all correct options
                $correctAnswers = $question->answers
                    ->where('is_correct', 1)
                    ->map(fn($ans) => [
                        'id'     => $ans->id,
                        'answer' => $ans->answer,
                    ])
                    ->values()
                    ->toArray();
            }


            $studentAnswer = null;
            $isCorrect = false;

            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                $selectedId = $attemptAnswer?->quiz_answer_id;

                if ($attemptAnswer?->quiz_answer_id) {
                    // Find the selected answer object for student answer
                    $selectedAnswer = $question->answers->firstWhere('id', $attemptAnswer->quiz_answer_id);
                    if ($selectedAnswer) {
                        $studentAnswer = [
                            'id' => $selectedAnswer->id,
                            'answer' => $selectedAnswer->answer,
                        ];
                    }
                }

                // Check if student's selected answer id is in correct answers
                $isCorrect = $correctAnswers && collect($correctAnswers)
                    ->pluck('id')
                    ->contains($attemptAnswer?->quiz_answer_id);
            } elseif ($question->type === 'short_answer') {
                // For short answer, student_answer is the text typed, with id null
                $studentAnswerText = $attemptAnswer?->answer_text ?? null;
                $studentAnswer = $studentAnswerText !== null ? [
                    'id' => null,
                    'answer' => $studentAnswerText,
                ] : null;


                  $correctAnswer = $question->expected_answer;
        if ($correctAnswer) {
            $normalizedStudent = $this->normalizeAnswer($studentAnswerText);
            $normalizedCorrect = $this->normalizeAnswer($correctAnswer);

            similar_text($normalizedStudent, $normalizedCorrect, $percent);

            // dd($normalizedStudent, $normalizedCorrect, $percent);

            if ($percent >= 90) {
                    $isCorrect = true;
            }
            }
            }

            $pointsAwarded = $isCorrect ? $question->points : 0;
            $totalScore += $pointsAwarded;

            $results[] = [
                'question_id'      => $question->id,
                'question_type'      => $question->type,
                'answer_percent' => round($attemptAnswer->answer_percent) . '%',

                'question'         => $question->question,
                'question_answers' => $question->answers->map(fn($ans) => [
                                                'id' => $ans->id,
                                                'answer' => $ans->answer_en,
                                                'is_correct' => (bool) $ans->is_correct, // optional
                                                'is_selected'=> $ans->id === $selectedId,

                                            ])->values()->toArray(),
                'student_answer'   => $studentAnswer,
                'correct_answers'  => $correctAnswers,
                'is_correct'       => $isCorrect,
                'points_awarded'   => $pointsAwarded,
                'points_possible'  => $question->points,
            ];
        }

        return $this->success('', [
            'class_id'=> $attempt->quiz->class_id,

            'section_id'=> $attempt->quiz->section_id,

            'course_id'=> $attempt->quiz->section->course_id ??$attempt->quiz->class->course_id,
            'attempt_id'   => $attempt->id,
            'quiz_title'   => $attempt->quiz->title_en,
            'score'        => $totalScore,
            'full_score'   => $fullScore,
            'submitted_at' => $attempt->submitted_at,
            'results'      => $results,
        ]);
}


function normalizeAnswer($text)
{
    return preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($text)));
}


}
