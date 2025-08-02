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

    $quiz = Quiz::with('course')->find($quizId);

    if (!$quiz) {
        return $this->failure(__('Quiz is not found'));
    }

    if (!$quiz->course || !$quiz->course->is_active) {
        return $this->failure('Quiz is not linked to an active course.');
    }

    if (!$quiz->course->isStudentEnrolled($studentId)) {
        return $this->failure('You are not enrolled in this course.');
    }
   if ($quiz->questions->isEmpty()) {
        return $this->failure(__('Quiz does not contain any questions.'));
    }
    if ($quiz->attempt_count !== null) {
        $usedAttempts = QuizAttempt::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            // ->whereNotNull('submitted_at')
            ->count();

        if ($usedAttempts >= $quiz->attempt_count) {
            return $this->failure(__('You have reached the maximum number of attempts.'));
        }
    }

    $quiz->increment('attempt');

    $attempt = QuizAttempt::where('quiz_id', $quizId)
        ->where('student_id', $studentId)
        ->whereNull('submitted_at')
        ->first();

    if ($attempt && $quiz->duration_minutes) {
        if ($attempt->started_at->addMinutes($quiz->duration_minutes)->isPast()) {
            $attempt->answers()->delete();
            // $attempt->delete();
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

    return $this->success('', [
        'attempt' => [
            'attempt_id' => $attempt->id,
            'student_id' => $studentId,
            'started_at' => $attempt->started_at->format('H:i:s'),
        ],
        'quiz' => new QuizResource($quiz),
    ]);
}


public function submitQuiz(Request $request, $quizAttemptId)
{
    $attempt = QuizAttempt::with('quiz.questions.answers')->findOrFail($quizAttemptId);
   if (!$attempt->quiz->course->isStudentEnrolled($attempt->student_id)) {
        return $this->failure('You are not enrolled in this course.');
    }
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
                    'course_id'=>$attempt->quiz?$attempt->quiz?->course->id:null,
                    'class_id'=>$attempt->quiz?$attempt->quiz?->class->id:null,

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

            $correctAnswer = $question->expected_answer;
        if ($correctAnswer) {
            $normalizedStudent = $this->normalizeAnswer($studentAnswer);
            $normalizedCorrect = $this->normalizeAnswer($correctAnswer);

            similar_text($normalizedStudent, $normalizedCorrect, $percent);

            // dd($normalizedStudent, $normalizedCorrect, $percent);

            if ($percent >= 80) {
                $score += $question->points;
            }

                $attemptAnswer->answer_percent=$percent;
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
             'course_id'=>$attempt->quiz?$attempt->quiz?->course->id:null,
            'class_id'=>$attempt->quiz?$attempt->quiz?->class?->id:null,

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
    'answers.answer'
    ])->where('id', $studentQuizId)
    ->where('student_id', auth()->id())
    ->first();

    if (!$attempt) {
        return $this->failure('Quiz attempt not found or access denied.');
    }

    if (!$attempt->quiz->course->isStudentEnrolled($attempt->student_id)) {
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

            if ($percent >= 80) {
                    $isCorrect = true;
            }
            }
            }

            $pointsAwarded = $isCorrect ? $question->points : 0;
            $totalScore += $pointsAwarded;

            $results[] = [
                'question_id'      => $question->id,
                'question_type'      => $question->type,
                'answer_percent' => round($question->answer_percent) . '%',

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
