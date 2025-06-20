<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassDetailsResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $student = auth('api')->user();
        $activeQuiz = $this->quizzes()->where('is_active', true)->first();

        // Check if the student attempted the *active* quiz at least once
        $hasAttemptedActiveQuiz = false;

        if ($student && $activeQuiz) {
            $hasAttemptedActiveQuiz = $activeQuiz->studentAttempts()
                ->where('student_id', $student->id)
                ->exists();
        }

        return [
               "id" => $this->id,
            'image' => $this->full_image_path,
            'title' => $this->title,
            'started_at' => $this->course->start_date,
            'quiz_required' => $hasAttemptedActiveQuiz ? 0 : $this->quiz_required,
            'attachment' => $this->full_attachment_path,
            'quiz_id'=>$activeQuiz->id??"not found Quiz",
        ];
    }
}
