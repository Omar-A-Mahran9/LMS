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

    return $this->success('',[ 'attempt' => $attempt,
        'quiz' => new QuizResource($quiz)]);
}


public function submitQuiz(Request $request, $quizAttemptId)
{
    dd('fdf');
    $attempt = QuizAttempt::with('quiz.questions.answers')->findOrFail($quizAttemptId);




    if ($attempt->quiz->duration_minutes && $attempt->started_at) {
        $expiryTime = \Carbon\Carbon::parse($attempt->started_at)->addMinutes($attempt->quiz->duration);
         if (now()->greaterThan($expiryTime)) {
            return response()->json([
                'message' => __('The quiz time has expired. You cannot submit your answers.'),
            ], 403);
        }
    }

    $data = $request->validate([
        'answers' => 'required|array',
    ]);

     // Validate that all questions are answered
    $expectedQuestionIds = $attempt->quiz->questions->pluck('id')->toArray();
    $submittedQuestionIds = array_keys($data['answers']);
    $missingQuestionIds = array_diff($expectedQuestionIds, $submittedQuestionIds);

    if (!empty($missingQuestionIds)) {
        return response()->json([
            'message' => 'You must answer all questions before submitting.',
            'missing_questions' => $missingQuestionIds,
        ], 422);
    }

    $score = 0;

    foreach ($attempt->quiz->questions as $question) {
        $studentAnswer = $data['answers'][$question->id] ?? null;

    // Validation: ensure answer exists in available options (for multiple choice and true/false)
        if (in_array($question->type, ['multiple_choice', 'true_false'])) {
            $validAnswerIds = $question->answers->pluck('id')->toArray();
            if (!in_array($studentAnswer, $validAnswerIds)) {
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

    $attempt->submitted_at = now();
    $attempt->score = $score;
    $attempt->save();


    return $this->success(__('Quiz submitted successfully'),[
        'score' => $score,
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
        'answers.answer'
    ])->findOrFail($studentQuizId);

    $results = [];
    $totalScore = 0;
    $fullScore = 0;

    foreach ($attempt->quiz->questions as $question) {
        $fullScore += $question->points;

        $attemptAnswer = $attempt->answers->firstWhere('quiz_question_id', $question->id);
        $correctAnswers = $question->answers->where('is_correct', 1)->pluck('answer_en', 'id')->toArray();

        $studentAnswer = null;
        $isCorrect = false;

        if (in_array($question->type, ['multiple_choice', 'true_false'])) {
            $studentAnswer = optional($attemptAnswer->answer)->answer_en ?? null;

            if ($attemptAnswer?->quiz_answer_id && array_key_exists($attemptAnswer->quiz_answer_id, $correctAnswers)) {
                $isCorrect = true;
            }
        } elseif ($question->type === 'short_answer') {
            $studentAnswer = $attemptAnswer->answer_text;

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
