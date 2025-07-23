<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'have_duration'=> $this->duration_minutes?true:false,
            "title" => $this->title,
             "description" => $this->description,
                     "course_id" => $this->course?$this->course->id:null,
        "class_id" => $this->class?$this->class->id:null,

            'duration_minutes' => $this->duration_minutes,
              'attempt_count' => $this->attempt_count,
        'remaining_attempts' => $this->attempt_count !== null
            ? max(0, $this->attempt_count - $attemptsMade)
            : null,
        'full_score' => $questions->sum('points'),
        'question_count' => $questions->count(),
            'questions' => $this->whenLoaded('questions')
            ? QuestionHomeworkResource::collection($this->questions)
            : [],        ];
    }
}
