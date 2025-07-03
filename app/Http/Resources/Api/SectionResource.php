<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    protected $studentId;

    public function __construct($resource, $studentId = null)
    {
        parent::__construct($resource);
        $this->studentId = $studentId ?? auth('api')->id();
    }

    public function toArray(Request $request): array
    {
        $studentId = $this->studentId;

        // Get first active quiz and homework
        $activeQuiz = $this->quizzes()->where('is_active', true)->first();
        $activeHomework = $this->homeworks()->where('is_active', true)->first();

        // Flags
        $hasAttemptedQuiz = false;
        $quizAttemptLimitReached = false;
        $hasAttemptedHomework = false;
        $homeworkAttemptLimitReached = false;

        // Check quiz attempts
        if ($studentId && $activeQuiz) {
            $hasAttemptedQuiz = $activeQuiz->attempts()
                ->where('student_id', $studentId)
                ->exists();

            if ($activeQuiz->attempt_count !== null) {
                $usedAttempts = $activeQuiz->attempts()
                    ->where('student_id', $studentId)
                    ->count();

                if ($usedAttempts >= $activeQuiz->attempt_count) {
                    $quizAttemptLimitReached = true;
                }
            }
        }

        // Check homework attempts
        if ($studentId && $activeHomework) {
            $hasAttemptedHomework = $activeHomework->attempts()
                ->where('student_id', $studentId)
                ->exists();

            if ($activeHomework->attempt_count !== null) {
                $usedAttempts = $activeHomework->attempts()
                    ->where('student_id', $studentId)
                    ->count();

                if ($usedAttempts >= $activeHomework->attempt_count) {
                    $homeworkAttemptLimitReached = true;
                }
            }
        }

        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'short_title'        => $this->short_title,

            'description'  => $this->description,
            'attachment'   => $this->full_attachment_path,

            // List of videos with progress
            'videos' => $this->videos->map(function ($video) {
                return new VideoResource($video, $this->studentId);
            }),

            // Flags
            'has_quizzes'    => (!$quizAttemptLimitReached && $this->quizzes()->exists() && $activeQuiz && $activeQuiz->questions()->exists()
) ? true : false,
            'has_homeworks'  => (!$homeworkAttemptLimitReached && $this->homeworks()->exists() && $activeHomework && $activeHomework->questions()->exists()) ? true : false,
            'quiz_required'  => (!$quizAttemptLimitReached && !$hasAttemptedQuiz && $activeQuiz && $activeQuiz->questions()->exists()) ? $this->quiz_required : 0,

            // IDs and attempts
            'quiz_id'            => !$quizAttemptLimitReached ? $activeQuiz?->id : null,
            'quiz_attempted'     => $hasAttemptedQuiz,
            'homework_id'        => !$homeworkAttemptLimitReached ? $activeHomework?->id : null,
            'homework_attempted' => $hasAttemptedHomework,
        ];
    }
}
