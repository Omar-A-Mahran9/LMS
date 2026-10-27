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
        // Video slides: a YouTube link (embedded) or a direct video file link (mp4 / webm ...)
        $embed = $this->is_video ? convertToYoutubeEmbed((string) $this->video_url) : null;
        $youtubeId = $embed ? basename($embed) : null;
        $mediaType = !$this->is_video ? 'image' : ($youtubeId ? 'youtube' : 'video');

        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "btn_name" => $this->btn_title,
            "btn_link" => $this->btn_link,
            "is_video"=> $this->is_video,
        'image_or_video' => $this->is_video ? ($embed ?? $this->video_url) : $this->full_image_path,
            'media_type' => $mediaType, // image | youtube | video
            'image' => $this->background ? $this->full_image_path : null,
            'video_url' => $mediaType === 'youtube' ? $embed : ($this->is_video ? $this->video_url : null),
            'youtube_id' => $youtubeId,
            // Cover shown before the video plays
            'poster' => $this->background
                ? $this->full_image_path
                : ($youtubeId ? "https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg" : null),

        ];
    }
}
