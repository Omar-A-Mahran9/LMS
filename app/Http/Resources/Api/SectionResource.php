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
        $this->studentId = $studentId;
    }

    public function toArray(Request $request): array
    {
    $student = auth('api')->user();

        // Get first active quiz and homework
    $activeQuiz = $this->quizzes()->where('is_active', true)->first();
    $activeHomework = $this->homeworks()->where('is_active', true)->first();

    // Default attempt flags
    $hasAttemptedQuiz = false;
    $hasAttemptedHomework = false;
    $quizAttemptLimitReached = false;

  if ($student && $activeQuiz) {
        $hasAttemptedQuiz = $activeQuiz->attempts()
            ->where('student_id', $student->id)
            ->exists();

        if ($activeQuiz->attempt_count !== null) {
            $usedAttempts = $activeQuiz->attempts()
                ->where('student_id', $student->id)
                ->count();

            if ($usedAttempts >= $activeQuiz->attempt_count) {
                $quizAttemptLimitReached = true;
            }
        }
    }

    if ($student && $activeHomework) {
        $hasAttemptedHomework = $activeHomework->attempts()
            ->where('student_id', $student->id)
            ->exists();
    }


        $activeQuiz = $this->quizzes()->where('is_active', true)->first();

        // Check if the student attempted the *active* quiz at least once
        $hasAttemptedActiveQuiz = false;

        if ($student && $activeQuiz) {
            $hasAttemptedActiveQuiz = $activeQuiz->attempts()
                ->where('student_id', $student->id)
                ->exists();
        }


        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'attachment'   => $this->full_attachment_path,

            // List of videos in the section with student-specific progress
            'videos' => $this->videos->map(function ($video) {

                return new VideoResource($video, $this->studentId);
            }),

            // Optional section-level flags
        'has_quizzes' => (!$quizAttemptLimitReached && $this->quizzes()->exists()) ? true : false,
            'has_homeworks' => $this->homeworks()->exists(),
            'quiz_required' => $hasAttemptedActiveQuiz ? 0 : $this->quiz_required,


           // First quiz & homework info
        'quiz_id'     => (!$quizAttemptLimitReached) ? $activeQuiz?->id : null,
        'quiz_required' => (!$quizAttemptLimitReached && !$hasAttemptedQuiz) ? $this->quiz_required : 0,

        'quiz_attempted' => $hasAttemptedQuiz,
        'homework_id'     => $activeHomework?->id ?? null,
        'homework_attempted' => $hasAttemptedHomework,
        ];
    }
}
