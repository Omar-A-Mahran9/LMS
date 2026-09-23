<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
     use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
        'started_at' => 'datetime',
         'submitted_at' => 'datetime',
        'answered_percent' => 'float',
    ];
    protected $dates = ['started_at', 'submitted_at'];


     public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    public function answer() {
        return $this->belongsTo(QuizAnswer::class, 'quiz_answer_id');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    // True when the quiz has a time limit and it's over for this attempt
    // ($graceSeconds > 0 tolerates late requests, < 0 treats "almost over" as over)
    public function isExpired(int $graceSeconds = 0): bool
    {
        $duration = $this->quiz?->duration_minutes;

        return $duration && $this->started_at
            && $this->started_at->copy()->addMinutes($duration)->addSeconds($graceSeconds)->isPast();
    }

    public function remainingSeconds(): ?int
    {
        $duration = $this->quiz?->duration_minutes;
        if (!$duration || !$this->started_at) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->started_at->copy()->addMinutes($duration), false));
    }

    // Did this (submitted) attempt answer enough questions to unlock the class?
    public function meetsRequiredPercent(): bool
    {
        return $this->isSubmitted() && $this->answered_percent >= Quiz::REQUIRED_ANSWERED_PERCENT;
    }

    /**
     * Save/overwrite the student's answers (one row per question) and refresh the progress counters.
     * $answers: [question_id => answer]  (answer id for MCQ/true_false, text for short_answer, null to clear)
     */
    public function storeAnswers(array $answers): void
    {
        $questions = $this->quiz->questions()->with('answers')->get()->keyBy('id');

        foreach ($answers as $questionId => $value) {
            $question = $questions->get($questionId);
            if (!$question) {
                continue;
            }

            $data = ['quiz_answer_id' => null, 'answer_text' => null, 'answer_percent' => null];

            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                if (is_numeric($value) && $question->answers->contains('id', (int) $value)) {
                    $data['quiz_answer_id'] = (int) $value;
                }
            } elseif ($question->type === 'short_answer') {
                $data['answer_text'] = is_string($value) && trim($value) !== '' ? $value : null;
            }

            $this->answers()->updateOrCreate(['quiz_question_id' => $question->id], $data);
        }

        $this->refreshProgress($questions);
    }

    public function refreshProgress($questions = null): void
    {
        $questions ??= $this->quiz->questions()->get();
        $total = $questions->count();

        $answered = $this->answers()
            ->whereIn('quiz_question_id', $questions->pluck('id'))
            ->where(function ($q) {
                $q->whereNotNull('quiz_answer_id')
                  ->orWhere(function ($q) {
                      $q->whereNotNull('answer_text')->where('answer_text', '!=', '');
                  });
            })
            ->distinct()
            ->count('quiz_question_id');

        $this->answered_count = $answered;
        $this->answered_percent = $total ? round($answered / $total * 100, 2) : 0;
        $this->save();
    }

    // Grade the saved answers and close the attempt
    public function finalize(): void
    {
        $questions = $this->quiz->questions()->with('answers')->get();
        $savedAnswers = $this->answers()->get()->keyBy('quiz_question_id');
        $score = 0;

        foreach ($questions as $question) {
            $saved = $savedAnswers->get($question->id);
            if (!$saved) {
                continue;
            }

            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                if ($saved->quiz_answer_id && $question->answers->where('is_correct', 1)->contains('id', $saved->quiz_answer_id)) {
                    $score += $question->points;
                }
            } elseif ($question->type === 'short_answer' && $question->expected_answer) {
                similar_text(self::normalizeAnswer($saved->answer_text), self::normalizeAnswer($question->expected_answer), $percent);
                $saved->update(['answer_percent' => $percent]);

                if ($percent >= 90) {
                    $score += $question->points;
                }
            }
        }

        $this->score = $score;
        $this->submitted_at = now();
        $this->refreshProgress($questions);
    }

    public static function normalizeAnswer($text): string
    {
        return preg_replace('/[^a-z0-9]+/i', '', strtolower(trim((string) $text)));
    }
}
