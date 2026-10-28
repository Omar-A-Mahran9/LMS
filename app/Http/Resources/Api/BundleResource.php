<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Never includes the access codes.
     */
    public function toArray(Request $request): array
    {
        $studentId = auth('api')->id();

        return [

            'id' => $this->id,

            'title' => $this->title,

            'description' => $this->description,

            'image' => $this->full_image_path,

            'price' => (float) $this->price,

            'discount_price' => $this->discount_price !== null ? (float) $this->discount_price : null,

            'final_price' => (float) $this->final_price,

            'have_discount' => $this->discount_price !== null && $this->discount_price < $this->price,

            'is_active' => $this->is_active,

            // false: the code alone opens the bundle, no account needed
            'requires_login' => $this->requires_login,

            // The logged-in student activated it: its classes open from /lessons/{id}
            'is_owned' => $this->isOwnedBy($studentId),

            'classes_count' => $this->classes_count,

            'classes' => ClassesDetailsResource::collection(
                $this->whenLoaded('classes')
            ),

            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
