<?php

namespace App\Http\Resources\Api;

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
            'question' => $this->question,
            'answers' => $this->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer' => $answer->answer, // or use localization: app()->getLocale() == 'ar' ? $answer->answer_ar : $answer->answer_en
                ];
            }),

            // expected_answer is the correct answer of short answer questions: never sent before submitting
            'type' => $this->type,
            // > 1 => the student can pick this many options
            'max_selections' => $this->maxSelections(),
            'points' => $this->points,
        ];
    }
}
