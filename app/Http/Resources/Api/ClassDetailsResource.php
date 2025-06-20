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

        // Check if student has submitted at least one quiz for this class
        $hasSubmittedQuiz = false;
        if ($student) {
            $hasSubmittedQuiz = $this->quizzes()
                ->whereHas('studentAttempts', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                          ->whereNotNull('submitted_at');
                })
                ->exists();
        }

        return [
               "id" => $this->id,
            'image' => $this->full_image_path,
            'title' => $this->title,
            'started_at' => $this->course->start_date,
            'quiz_required' => $hasSubmittedQuiz ? 0 : $this->quiz_required,
            'attachment' => $this->full_attachment_path,
            'quiz_id'=>$activeQuiz->id??"not found Quiz",
        ];
    }
}
