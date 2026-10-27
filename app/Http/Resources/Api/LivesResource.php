<?php

namespace App\Http\Resources\Api;

use App\Models\Live;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public summary of a live (home / grade / class cards): never includes the stream or meeting links.
 */
class LivesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $course = $this->course;

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'image'       => $this->full_image_path,
            'platform'    => $this->platform,
            'status'      => $this->status,
            'is_on_air'   => $this->status === Live::STATUS_LIVE,
            'start_at'    => $this->startsAt()?->toIso8601String(),
            'end_at'      => $this->endsAt()?->toIso8601String(),
            'course_id'   => $this->course_id,
            'course_title' => $course?->title,
            'class_id'    => $this->class_id,
            'class_title' => $this->class?->title,
            'category_id' => $course?->category_id,
            'category_name' => $course?->category?->name,
            // Website page that shows the live (the class page, else the course's classes)
            'link'        => $this->class_id
                ? "/lessons/{$this->class_id}"
                : ($course ? StudentNotifier::courseLink($course) : null),
            'chat_enabled' => (bool) $this->chat_enabled,
            // kept for older clients
            'has_live'    => $this->status === Live::STATUS_LIVE,
        ];
    }
}
