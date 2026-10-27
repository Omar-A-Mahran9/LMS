<?php

namespace App\Http\Resources\Api;

use App\Models\CourseVideoStudent;
use App\Models\Live;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassesDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            $student = Auth::guard('api')->user();
        $isEnrolled = false;

        if ($student) {
            $isEnrolled = DB::table('class_student')
                ->where('class_id', $this->id)
                ->where('student_id', $student->id)
                ->exists();
        }
        // Watch progress of the student in this class (active videos only)
        $videoIds = $this->videos()->where('is_active', 1)->pluck('id');
        $completedVideos = $student && $videoIds->isNotEmpty()
            ? CourseVideoStudent::whereIn('course_video_id', $videoIds)
                ->where('student_id', $student->id)
                ->where('is_completed', true)
                ->count()
            : 0;

        // Live hint on the class card: on air now, or the next one coming
        $live = $this->currentLive();
        $liveIds = $this->lives()
            ->where('is_active', true)
            ->pluck('id');
        return [
               "id" => $this->id,
            'image' => $this->full_image_path,
            'has_live'=> $live?->status === Live::STATUS_LIVE,
            'live' => $live ? new LivesResource($live) : null,
            'live_ids' => $liveIds,

            'title' => $this->title,
            'started_at' => $this->course->start_date,
            'course_title' => $this->course?->title,
            'category_id' => $this->course?->category_id,
            'category_name' => $this->course?->category?->name,
            'quiz_required'=>$this->quiz_required,
            'is_enrolled' => $isEnrolled,
            'videos_count' => $videoIds->count(),
            'completed_videos' => $completedVideos,
            'progress' => $videoIds->count() ? (int) round($completedVideos / $videoIds->count() * 100) : 0,

        ];
    }
}
