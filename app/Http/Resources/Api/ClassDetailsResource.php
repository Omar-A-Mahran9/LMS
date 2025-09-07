<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassDetailsResource extends JsonResource
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
// Find next class in the same course
$nextClass = $this->course
    ? $this->course->classes()
        ->where('id', '>', $this->id)
        ->orderBy('id', 'asc')
        ->first()
    : null;

        $student = auth('api')->user();
        $activeQuiz = $this->quizzes()->where('is_active', true)->first();
        $activeHomework = $this->homeworks()->where('is_active', true)->first();

        // Check if the student attempted the *active* quiz at least once
        $hasAttemptedActiveQuiz = false;

        if ($student && $activeQuiz) {
            $hasAttemptedActiveQuiz = $activeQuiz->attempts()
                ->where('student_id', $student->id)
                ->exists();
        }
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
      $hasLive = $this->lives()
            ->where('is_active', 1)
            ->exists();
    $liveIds = $this->lives()
        ->where('is_active', true)
        ->pluck('id'); // 👈 get all active live IDs
        return [
            "id" => $this->id,
            'next_class_id' => $nextClass?->id,
            'image' => $this->full_image_path,
            'title' => $this->title,
            'short_title'        => $this->short_title,
                        'course_id'        => $this->course_id,
            'has_live'=>$hasLive,
                        'live_ids'=>$liveIds,

            'description'  => $this->description,
            'started_at' => $this->course->start_date,
            // 'quiz_required' => $hasAttemptedActiveQuiz ? 0 : $this->quiz_required,
            'attachment' => $this->full_attachment_path,
            'quiz_id'=>$activeQuiz->id??"not found Quiz",
   'videos' => $this->videos->map(function ($video) {
                return new VideoResource($video, $this->studentId);
            }),


            // Flags
            'has_quizzes'    => (!$quizAttemptLimitReached && $this->quizzes()->exists() && $activeQuiz && $activeQuiz->questions()->exists()
) ? true : false,
             // 'has_homeworks'  => (!$homeworkAttemptLimitReached && $this->homeworks()->exists() && $activeHomework && $activeHomework->questions()->exists()) ? true : false,
            'quiz_required'  => (!$quizAttemptLimitReached && !$hasAttemptedQuiz && $activeQuiz && $activeQuiz->questions()->exists()) ? $this->quiz_required : 0,

            // IDs and attempts
            'quiz_id'            => !$quizAttemptLimitReached ? $activeQuiz?->id : null,
            'quiz_attempted'     => $hasAttemptedQuiz,
            'homework_id'        => !$homeworkAttemptLimitReached ? $activeHomework?->id : null,
            'homework_attempted' => $hasAttemptedHomework,

        ];
    }
}
