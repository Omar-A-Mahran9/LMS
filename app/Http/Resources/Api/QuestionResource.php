<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'question_en' => $this->question,
            'answers' => $this->answers,

            'expected_answer' => $this->expected_answer,  // might be NULL for multiple_choice/true_false
            'type' => $this->type,
            'points' => $this->points,
        ];
    }
}
