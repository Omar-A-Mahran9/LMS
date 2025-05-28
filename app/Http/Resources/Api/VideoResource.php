<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user(); // Get authenticated user or null

    $showVideoUrl =
        ($user && $this->is_active)  // Authenticated user + active
        || ($this->is_active && $this->is_preview); // Active + preview for guests

      return [
            'id' => $this->id,
            'title' => $this->title,
            'is_preview' => $this->is_preview,
            'is_active' => $this->is_active,
            'video_url' => $showVideoUrl ? base64_encode($this->video_url) : null,
          // add other fields you want to expose
        ];
    }
}
