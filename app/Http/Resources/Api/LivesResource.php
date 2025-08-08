<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LivesResource extends JsonResource
{
public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'course_id'       => $this->course_id,
            'class_id'        => $this->class_id,
            'chat_enabled'    => (bool) $this->chat_enabled,
            'chat_embed_url'  => $this->chat_enabled ? $this->chat_embed_url : null,
            'video_embed_url' => $this->video_embed_url,
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}
