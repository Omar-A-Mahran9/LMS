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
            'question' => $this->question,
            'answers' => $this->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer' => $answer->answer, // or use localization: app()->getLocale() == 'ar' ? $answer->answer_ar : $answer->answer_en
                ];
            }),

            'expected_answer' => $this->expected_answer,  // might be NULL for multiple_choice/true_false
            'type' => $this->type,
            'points' => $this->points,
        ];
    }
}
