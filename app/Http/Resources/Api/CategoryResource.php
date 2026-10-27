<?php

namespace App\Http\Resources\Api;

use App\Models\Live;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Live hint on the grade card: on air now, or the next one coming in this grade
        $live = Live::published()
            ->current()
            ->forCategory($this->id)
            ->with(['course.category', 'class'])
            ->soonestFirst()
            ->first();

        return [
            "id"=> $this->id,
            "image"=> $this->full_image_path,
            "name"=> $this->name,
            "count_of_courses" => $this->courses->count(),
            "has_live" => $live?->status === Live::STATUS_LIVE,
            "live" => $live ? new LivesResource($live) : null,
         ];
    }
}
