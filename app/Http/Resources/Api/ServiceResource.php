<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            'image' => $this->full_image_path,
            'icon' => $this->full_icon_path,
            'price' => $this->price,
            'visiting_price' => $this->visiting_price,
            'have_price_after_visiting' => $this->have_price_after_visiting,
            'name' => $this->name,
            'description' => $this->description

        ];
    }
}
