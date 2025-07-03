<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

         // Get current authenticated student (assuming you're using sanctum or similar)
        $studentId = auth('api')->id(); // or however you auth students

        // Count the number of attempts made by this student
        $attemptsMade = $this->attempts()
            ->where('student_id', $studentId)
            ->count();
        $questions = $this->relationLoaded('questions') ? $this->questions : $this->questions()->get();

        return [
            "id" => $this->id,
            'have_duration'=> $this->duration_minutes?true:false,
            'duration_minutes' => $this->duration_minutes,
            'attempt_count' => $this->attempt_count,
             'remaining_attempts' => $this->attempt_count !== null
                ? max(0, $this->attempt_count - $attemptsMade)
                : null, // null if unlimited
            'full_score' => $questions->sum('points'),
            'question_count' => $questions->count(),

            'questions' => $this->whenLoaded('questions')
            ? QuestionResource::collection($this->questions)
            : [],        ];
    }
}
