<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
            "title" => $this->title,
            "description" => $this->description,
            "btn_name" => $this->btn_title,
            "btn_link" => $this->btn_link,
            "is_video"=> $this->is_video,
        'image_or_video' => $this->is_video ? convertToYoutubeEmbed($this->video_url) : $this->full_image_path,

        ];
    }
}
