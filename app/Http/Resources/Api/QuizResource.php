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
        return [
            "id" => $this->id,
            'have_duration'=> $this->duration_minutes?true:false,
            'duration_minutes' => $this->duration_minutes,
            'questions' => $this->whenLoaded('questions')
            ? QuestionResource::collection($this->questions)
            : [],        ];
    }
}
