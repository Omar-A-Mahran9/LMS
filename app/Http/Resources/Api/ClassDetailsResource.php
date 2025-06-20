<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $student = auth('api')->user();

        $activeQuiz = $this->quizzes()->where('is_active', true)->first();

        // هل الطالب جرب يحل أي كويز لهذا الكلاس؟
        $hasAttemptedQuiz = false;
        if ($student) {
            $hasAttemptedQuiz = $this->quizzes()
                ->whereHas('studentAttempts', function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                })
                ->exists();
        }

        return [
            "id" => $this->id,
            'image' => $this->full_image_path,
            'title' => $this->title,
            'started_at' => $this->course->start_date,
            'quiz_required' => $hasAttemptedQuiz ? 0 : $this->quiz_required,
            'attachment' => $this->full_attachment_path,
            'quiz_id' => $activeQuiz->id ?? "not found Quiz",
        ];
    }
}
