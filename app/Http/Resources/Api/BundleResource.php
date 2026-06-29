<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'title' => app()->getLocale() == 'ar'
                ? $this->title_ar
                : $this->title_en,

            'title_ar' => $this->title_ar,

            'title_en' => $this->title_en,

            'description_ar' => $this->description_ar,

            'description_en' => $this->description_en,

            'image' => $this->full_image_path,

            'is_active' => $this->is_active,

            'classes_count' => $this->classes_count,

            'codes_count' => $this->codes_count,

            'classes' => ClassesDetailsResource::collection(
                $this->whenLoaded('classes')
            ),

            'created_at' => $this->created_at,
        ];
    }
}
