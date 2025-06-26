<?php

namespace App\Http\Resources\Api;

use App\Models\CourseVideoStudent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class CoursesDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $studentId = auth('api')->id();

        // جميع فيديوهات الكورس
        $totalVideos = $this->videos->count();

        // عدد الفيديوهات التي أكملها الطالب
        $completedCount = CourseVideoStudent::whereIn('course_video_id', $this->videos->pluck('id'))
            ->where('student_id', $studentId)
            ->where('is_completed', true)
            ->count();

        $isCompleted = $totalVideos > 0 && $completedCount === $totalVideos;
        $progressPercentage = $totalVideos > 0
            ? round(($completedCount / $totalVideos) * 100)
            : 0;

        return [
            'id' => $this->id,
            'image' => $this->full_image_path,
            'video_url' => base64_encode(convertToYoutubeEmbed($this->video_url)),
            'title' => $this->title,
            'started_at' => $this->start_date,
            'count_video' => $totalVideos,
            'category' => new CategoryResource($this->category),
            'phone' => setting('sms_number'),
            'is_class'=> $this->is_class,
            'price' => $this->is_free
                ? 'Free'
                : ($this->have_discount && $this->discount_percentage
                    ? round($this->price * (1 - $this->discount_percentage / 100), 2)
                    : $this->price),

            'original_price' => $this->price,
            'discount_percentage' => $this->have_discount ? $this->discount_percentage : null,
            'is_free' => $this->is_free,
            'have_discount' => $this->have_discount,
            'is_enrolled' => $this->is_enrolled,
            'payment_type' => $this->payment_type,

            'request_status' => [
                'key' => $this->request_status,
                'value' => __($this->request_status),
            ],

            'is_completed' => $isCompleted,
            'progress_percentage' => $progressPercentage,
            'certificate_url' => $isCompleted && $this->certificate_available
                ? route('student.certificates.download', ['course' => $this->id])
                : null,
        ];
    }
}
