<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HomeworkResource;
use App\Models\HomeWork;
use App\Models\HomeWorkAttempt;
use App\Models\HomeWorkAttemptAnswer;
use Illuminate\Http\Request;

class StudentHomeworkController extends Controller
{
    // Start or continue a homework attempt
    public function startHomework(Request $request, $homeworkId)
    {
        $studentId = auth()->id();

        $homework = HomeWork::findOrFail($homeworkId);

        $homework->increment('attempt');

        $attempt = HomeworkAttempt::where('student_id', $studentId)
            ->where('home_work_id', $homeworkId)
            ->whereNull('submitted_at')
            ->first();

        $durationMinutes = $homework->duration_minutes;

        if ($attempt) {
            // Only check for expiration if duration is set
            if ($durationMinutes !== null) {
                $expired = $attempt->started_at->addMinutes($durationMinutes)->isPast();

                if ($expired) {
                    // Reset attempt: delete old attempt and its answers
                    $attempt->answers()->delete();
                    $attempt->delete();
                    $attempt = null;
                }
            }

            // If duration is null, it's open — do nothing
        }


        if (!$attempt) {
            $attempt = HomeWorkAttempt::create([
                'home_work_id' => $homeworkId,
                'student_id' => $studentId,
                'started_at' => now(),
            ]);
        }

        return $this->success('', [
            'attempt' => [
                'attempt_id' => $attempt->id,
                'started_at' => $attempt->started_at->toDateTimeString(),
            ],
            'homework' => new HomeworkResource($homework)
        ]);
    }

    // Submit homework
    public function submitHomework(Request $request, $attemptId)
    {
        $attempt = HomeworkAttempt::with('homework.questions.answers')->findOrFail($attemptId);

        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*.id' => 'required|integer|exists:home_work_questions,id',
            'answers.*.answer' => 'nullable',
        ]);

        $answersAssoc = collect($data['answers'])->mapWithKeys(fn($item) => [$item['id'] => $item['answer']])->toArray();

        $expectedQuestionIds = $attempt->homework->questions->pluck('id')->toArray();
        $missingQuestionIds = array_diff($expectedQuestionIds, array_keys($answersAssoc));

        if (!empty($missingQuestionIds)) {
            return response()->json([
                'message' => __('You must answer all questions before submitting.'),
                'missing_questions' => $missingQuestionIds,
            ], 422);
        }

        $score = 0;
        foreach ($attempt->homework->questions as $question) {
            $studentAnswer = $answersAssoc[$question->id] ?? null;

            $answerRecord = new HomeWorkAttemptAnswer([
                'home_work_question_id' => $question->id,
            ]);

            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                $answerRecord->home_work_answer_id = $studentAnswer;
                $answerText = $question->answers->firstWhere('id', $studentAnswer)?->answer_en;
                if ($this->checkAnswer($question, $answerText)) {
                    $score += $question->points;
                }
            } elseif ($question->type === 'short_answer') {
                $answerRecord->answer_text = $studentAnswer;
                if ($this->checkAnswer($question, $studentAnswer)) {
                    $score += $question->points;
                }
            }

            $attempt->answers()->save($answerRecord);
        }

        $attempt->score = $score;
        $attempt->submitted_at = now();
        $attempt->save();

        $totalPoints = $attempt->homework->questions->sum('points');

        return $this->success(__('Homework submitted successfully'), [
            'score' => $score,
            'total_points' => $totalPoints,
            'score_text' => "{$score}/{$totalPoints}",
        ]);
    }

    // Get homework results
    public function results($attemptId)
    {
        $attempt = HomeworkAttempt::with([
            'homework.questions.answers',
            'answers.answer'
        ])->findOrFail($attemptId);

        $results = [];
        $score = 0;
        $total = 0;
         foreach ($attempt->homework->questions as $question) {
            $total += $question->points;

            $attemptAnswer = $attempt->answers->firstWhere('home_work_question_id', $question->id);
            $studentAnswer = null;
            $isCorrect = false;

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
            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                 $selectedId = $attemptAnswer?->quiz_answer_id;
                $selectedAnswer = $question->answers->firstWhere('id', $attemptAnswer?->home_work_answer_id);
                $studentAnswer = $selectedAnswer ? ['id' => $selectedAnswer->id, 'answer' => $selectedAnswer->answer_en] : null;
                $isCorrect = $correctAnswers && collect($correctAnswers)->pluck('id')->contains($attemptAnswer?->home_work_answer_id);
            } elseif ($question->type === 'short_answer') {
                $studentAnswer = [
                    'id' => null,
                    'answer' => $attemptAnswer?->answer_text,
                ];
                $isCorrect = strtolower(trim($studentAnswer['answer'] ?? '')) === strtolower(trim($question->expected_answer));
            }

            $pointsAwarded = $isCorrect ? $question->points : 0;
            $score += $pointsAwarded;

            $results[] = [
                'question_id' => $question->id,
                'question_type'      => $question->type,

                'question' => $question->question,
                'question_answers' => $question->answers->map(fn($ans) => [
                                            'id' => $ans->id,
                                            'answer' => $ans->answer_en,
                                            'is_correct' => (bool) $ans->is_correct, // optional
                                            'is_selected'=> $ans->id === $selectedId,
                                        ])->values()->toArray(),
                'student_answer' => $studentAnswer,
                'correct_answers' => $correctAnswers,
                'is_correct' => $isCorrect,
                'points_awarded' => $pointsAwarded,
                'points_possible' => $question->points,
            ];
        }

        return $this->success('', [
            'attempt_id' => $attempt->id,
            'homework_title' => $attempt->homework->title_en,
            'score' => $score,
            'full_score' => $total,
            'submitted_at' => $attempt->submitted_at,
            'results' => $results,
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
            $expected = strtolower(trim($question->expected_answer));
            $actual = strtolower(trim($studentAnswer));
            return $expected && $actual && str_contains($actual, $expected);
            default:
                return false;
        }
    }
}
