<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
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
            $activeQuiz = $this->quizzes()->where('is_active', true)->first();

        return [
               "id" => $this->id,
            'image' => $this->full_image_path,
            'title' => $this->title,
            'started_at' => $this->course->start_date,
            'quiz_required'=>$this->quiz_required,
            'attachment' => $this->full_attachment_path,
            'quiz_id'=>$activeQuiz->id,

        ];
    }
}
