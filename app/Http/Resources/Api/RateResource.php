<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RateResource extends JsonResource
{
  public function toArray($request)
{
    return [
        'id' => $this->id,
        'rate' => $this->rate,
        'image' => $this->full_image_path,
        'name' => $this->full_name,
        'created_at' => $this->created_at->toDateString(),
        'updated_at' => $this->updated_at->toDateString(),
        'content' => $this->text ?? $this->audio_full_path,
        'is_sound' => $this->text ? 0 : 1,

        // Only return 'text' if available; otherwise return 'audio'
    ];
}

}
