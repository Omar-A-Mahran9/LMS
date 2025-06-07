<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\QuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;

class StudentQuizController extends Controller
{
    // Start a quiz attempt or get existing attempt
 public function startQuiz(Request $request, $quizId)
{
    $studentId = auth()->id();

    $quiz = Quiz::findOrFail($quizId);

    $quiz->increment('attempt');

    // Find existing attempt
    $attempt = QuizAttempt::where('student_id', $studentId)
        ->where('quiz_id', $quizId)
        ->whereNull('submitted_at') // only active attempts
        ->first();

    $durationMinutes = $quiz->duration_minutes;

    if ($attempt && $durationMinutes) {
        // Check if attempt expired
        $expired = $attempt->started_at->addMinutes($durationMinutes)->isPast();

        if ($expired) {
            // Reset attempt (or delete & create new)
            // Here I prefer to delete and create new for clarity:
            $attempt->answers()->delete();
            $attempt->delete();
            $attempt = null;
        }
    }

    if (!$attempt) {
        $attempt = QuizAttempt::create([
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'started_at' => now(),
        ]);
    }
// Select only needed fields from $attempt
    $attemptData = [
        'attempt_id' => $attempt->id,
        'student_id' => $attempt->student_id,
        'started_at' => $attempt->started_at->format('H:i:s'),
    ];
        return $this->success('',[ 'attempt' => $attemptData,
            'quiz' => new QuizResource($quiz)]);
}


public function submitQuiz(Request $request, $quizAttemptId)
{
    $attempt = QuizAttempt::with('quiz.questions.answers')->findOrFail($quizAttemptId);

// Check if quiz duration expired
if ($attempt->quiz->duration_minutes && $attempt->started_at) {
    $expiryTime = \Carbon\Carbon::parse($attempt->started_at)->addMinutes($attempt->quiz->duration_minutes);
    if (now()->greaterThan($expiryTime)) {

        $score = 0;
        $totalPoints = $attempt->quiz->questions->sum('points');

        $scoredQuestionIds = [];

        foreach ($attempt->answers as $answer) {
            $question = $attempt->quiz->questions->firstWhere('id', $answer->quiz_question_id);
            if (!$question) continue;

            // Avoid scoring the same question multiple times
            if (in_array($question->id, $scoredQuestionIds)) {
                continue;
            }

            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                $correctAnswer = $question->answers->firstWhere('is_correct', 1);
                if ($correctAnswer && $answer->quiz_answer_id == $correctAnswer->id) {
                    $score += $question->points;
                    $scoredQuestionIds[] = $question->id;
                }
            } elseif ($question->type === 'short_answer') {
                if ($this->checkAnswer($question, $answer->answer_text)) {
                    $score += $question->points;
                    $scoredQuestionIds[] = $question->id;
                }
            }
        }

        $scoreText = "{$score}/{$totalPoints}";

        return $this->success(
            'The quiz time has expired. You cannot submit your answers.',
            [
                'score' => $score,
                'total_points' => $totalPoints,
                'score_text' => $scoreText,
            ]
        );
    }
}


    // Validate answers input as array of objects with id and answer (answer can be nullable)
    $data = $request->validate([
        'answers' => 'required|array',
        'answers.*.id' => 'required|integer|exists:quiz_questions,id',
        'answers.*.answer' => 'nullable',
    ]);

    // Map answers array to associative [question_id => answer]
    $answersAssoc = collect($data['answers'])->mapWithKeys(fn($item) => [$item['id'] => $item['answer']])->toArray();

    // Check all questions answered
    $expectedQuestionIds = $attempt->quiz->questions->pluck('id')->toArray();
    $submittedQuestionIds = array_keys($answersAssoc);
    $missingQuestionIds = array_diff($expectedQuestionIds, $submittedQuestionIds);

    if (!empty($missingQuestionIds)) {
        $missingWithCorrectAnswers = [];

        foreach ($missingQuestionIds as $missingId) {
            $question = $attempt->quiz->questions->firstWhere('id', $missingId);

            if ($question) {
                if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                    $correctAnswer = $question->answers->firstWhere('is_correct', 1);
                    $correctAnswerId = $correctAnswer ? $correctAnswer->id : null;
                    $missingWithCorrectAnswers[] = [
                        'id' => $missingId,
                        'answer' => $correctAnswerId,
                    ];
                } elseif ($question->type === 'short_answer') {
                    $correctAnswerText = $question->correct_answer ?? null;
                    $missingWithCorrectAnswers[] = [
                        'id' => $missingId,
                        'answer' => $correctAnswerText,
                    ];
                } else {
                    $missingWithCorrectAnswers[] = [
                        'id' => $missingId,
                        'answer' => null,
                    ];
                }
            } else {
                $missingWithCorrectAnswers[] = [
                    'id' => $missingId,
                    'answer' => null,
                ];
            }
        }

        return response()->json([
            'message' => __('You must answer all questions before submitting.'),
            'missing_questions' => $missingWithCorrectAnswers,
        ], 422);
    }

    $score = 0;

    foreach ($attempt->quiz->questions as $question) {
        $studentAnswer = $answersAssoc[$question->id] ?? null;

        // Validate multiple choice and true/false answers only if not null
        if (in_array($question->type, ['multiple_choice', 'true_false'])) {
            $validAnswerIds = $question->answers->pluck('id')->toArray();

            if ($studentAnswer !== null && !in_array($studentAnswer, $validAnswerIds)) {
                return $this->failure(__("Invalid answer submitted for question ID {$question->id}."));
            }
        }

        $attemptAnswer = new QuizAttemptAnswer([
            'quiz_question_id' => $question->id,
        ]);

        if (in_array($question->type, ['multiple_choice', 'true_false'])) {
            if (is_numeric($studentAnswer)) {
                $attemptAnswer->quiz_answer_id = $studentAnswer;
                $answerText = $question->answers->firstWhere('id', $studentAnswer)?->answer_en;
                if ($this->checkAnswer($question, $answerText)) {
                    $score += $question->points;
                }
            }
        } elseif ($question->type === 'short_answer') {
            $attemptAnswer->answer_text = $studentAnswer;
            if ($this->checkAnswer($question, $studentAnswer)) {
                $score += $question->points;
            }
        }

        $attempt->answers()->save($attemptAnswer);
    }
$totalPoints = $attempt->quiz->questions->sum('points');

    $attempt->submitted_at = now();
    $attempt->score = $score;
    $attempt->save();

        // Prepare score text like "earnedPoints/totalPoints"
        $scoreText = "{$score}/{$totalPoints}";

        return $this->success(__('Quiz submitted successfully'), [
            'score' => $score,
            'total_points' => $totalPoints,
            'score_text' => $scoreText,
        ]);
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
    $attempt = QuizAttempt::with([
        'quiz.questions.answers',
        'answers.answer' // assuming 'answer' relation on QuizAttemptAnswer links to QuizAnswer model
    ])->findOrFail($studentQuizId);

    $results = [];
    $totalScore = 0;
    $fullScore = 0;

    foreach ($attempt->quiz->questions as $question) {
        $fullScore += $question->points;

        $attemptAnswer = $attempt->answers->firstWhere('quiz_question_id', $question->id);

        // Format correct_answers as array of objects with id and answer
        $correctAnswers = $question->answers
            ->where('is_correct', 1)
            ->map(fn($ans) => [
                'id' => $ans->id,
                'answer' => $ans->answer_en,
            ])
            ->values()
            ->toArray();

        $studentAnswer = null;
        $isCorrect = false;

        if (in_array($question->type, ['multiple_choice', 'true_false'])) {
            // Get student's selected answer text if any
            $studentAnswer = optional($attemptAnswer?->answer)->answer_en ?? null;

            // Check if student's selected answer is correct
            if ($attemptAnswer?->quiz_answer_id) {
                $isCorrect = $question->answers
                    ->where('is_correct', 1)
                    ->pluck('id')
                    ->contains($attemptAnswer->quiz_answer_id);
            }
        } elseif ($question->type === 'short_answer') {
            $studentAnswer = $attemptAnswer?->answer_text ?? null;

            if ($studentAnswer && strtolower(trim($studentAnswer)) === strtolower(trim($question->expected_answer))) {
                $isCorrect = true;
            }
        }

        $pointsAwarded = $isCorrect ? $question->points : 0;
        $totalScore += $pointsAwarded;

        $results[] = [
            'question_id'      => $question->id,
            'question'         => $question->question,
            'student_answer'   => $studentAnswer,
            'correct_answers'  => $correctAnswers,
            'is_correct'       => $isCorrect,
            'points_awarded'   => $pointsAwarded,
            'points_possible'  => $question->points,
        ];
    }

    return $this->success('', [
        'attempt_id'   => $attempt->id,
        'quiz_title'   => $attempt->quiz->title_en,
        'score'        => $totalScore,
        'full_score'   => $fullScore,
        'submitted_at' => $attempt->submitted_at,
        'results'      => $results,
    ]);
}



}
