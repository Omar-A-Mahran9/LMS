<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoursesDetailsResource extends JsonResource
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
            'image' => $this->full_image_path,
            'title' => $this->title,
            'started_at' => $this->start_date,
            'count_video' => $this->videos->count(),
            'category' =>new CategoryResource ($this->category),
            'sub_category' => CategoryResource::collection($this->subCategories),

            'price' => $this->is_free
                ? 'Free' // or 0 if you prefer numeric value
                : ($this->have_discount && $this->discount_percentage
                    ? round($this->price * (1 - $this->discount_percentage / 100), 2) // discounted price
                    : $this->price
                ),


            'original_price' => $this->price,
            'discount_percentage' => $this->have_discount ? $this->discount_percentage : null,
            'is_free' => $this->is_free,
            'have_discount' => $this->have_discount,

        ];
    }
}
