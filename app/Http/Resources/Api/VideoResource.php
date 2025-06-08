<?php

namespace App\Http\Resources\Api;

use App\Models\CourseVideoStudent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    protected $studentId;

    // Optionally pass student ID or model to constructor
    public function __construct($resource, $studentId = null)
    {
        parent::__construct($resource);
        $this->studentId = $studentId;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Get student progress if available
        $progress = $this->getStudentProgress();
        if (!$progress) {
                $statusText = 'not_started';
            } elseif ($progress->is_completed) {
                $statusText = 'completed';
            } elseif ($progress->watch_seconds > 0) {
                $statusText = 'in_progress';
            } else {
                $statusText = 'not_started';
            }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->formatDuration($this->duration_seconds),

            // 'watch_seconds'        => $progress ? $this->formatDuration($progress->watch_seconds) : '0s',
            // 'last_watched_second'  => $progress ? $progress->last_watched_second : 0,
            // 'is_completed'         => $progress ? (bool)$progress->is_completed : false,
            // 'views'                => $progress ? $progress->views : 0,
            'status'               => $statusText,
            

            'thumbnail' => $this->full_image_path,
            'is_preview' => $this->is_preview,
            'is_active' => $this->is_active,
            'video_url' => base64_encode($this->video_url),
        ];
    }

    private function getStudentProgress()
    {
        if (!$this->studentId) {
            return null;
        }

        // Assumes you have a relationship on CourseVideo model:
        // public function studentProgress() { return $this->hasOne(CourseVideoStudent::class, 'course_video_id')->where('student_id', $this->studentId); }
        // Or you can fetch manually here:

        return CourseVideoStudent::where('course_video_id', $this->id)
                    ->where('student_id', $this->studentId)
                    ->first();
    }

    private function formatDuration($seconds)
    {
        if (!$seconds) return '0s';

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($remainingSeconds > 0 || empty($parts)) $parts[] = "{$remainingSeconds}s";

        return implode(' ', $parts);
    }
}
