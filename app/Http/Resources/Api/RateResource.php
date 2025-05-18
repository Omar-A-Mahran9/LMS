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
            'rate' => $this->rate, // Assuming 'rate' is a field in the Rate model
            'image' => $this->full_image_path,
            'audio' => $this->audio_full_path,
            'name'=>$this->full_name,
            'created_at' => $this->created_at->toDateString(),
            'updated_at' => $this->updated_at->toDateString(),
        ];
    }
}
