<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LivesResource extends JsonResource
{
public function toArray(Request $request): array
    {
            $now = Carbon::now();

        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'image' => $this->full_image_path,
            'course_id'       => $this->course_id,
            'class_id'        => $this->class_id,
            'chat_enabled'    => (bool) $this->chat_enabled,
            'start_at'   => $this->start_time,
            'end_at'   => $this->start_time,
            'stream' => $now->between(Carbon::parse($this->start_time), Carbon::parse($this->end_time)),

        ];
    }
}
