<?php

namespace App\Http\Resources\Api;

use App\Models\CourseVideoStudent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseStatusResource extends JsonResource
{
public function toArray(Request $request): array
    {
        $studentId = auth('api')->id();

        // هل الطالب أكمل جميع الفيديوهات؟
        $videoIds = $this->videos->pluck('id');
        $completedCount = CourseVideoStudent::whereIn('course_video_id', $videoIds)
            ->where('student_id', $studentId)
            ->where('is_completed', true)
            ->count();

        $isCompleted = $videoIds->count() > 0 && $completedCount === $videoIds->count();

        return [
            'is_enrolled'     => $this->is_enrolled,
            'is_completed'    => $isCompleted,
            'certificate_url' => $this->certificate_available && $isCompleted
                ? route('api.student.certificates.download', ['course' => $this->id])
                : null,
        ];
    }
}
