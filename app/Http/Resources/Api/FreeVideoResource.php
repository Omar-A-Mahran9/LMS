<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Free video / tip: public, so the YouTube id is sent as is.
 */
class FreeVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'image'         => $this->full_image_path,
            'youtube_id'    => $this->youtube_id,
            'duration_seconds' => $this->duration_seconds,
            'views'         => (int) $this->views,
            'is_featured'   => (bool) $this->is_featured,
            'category_id'   => $this->category_id,
            'category_name' => $this->category?->name,
            'created_at'    => $this->created_at?->format('Y-m-d'),
        ];
    }
}
