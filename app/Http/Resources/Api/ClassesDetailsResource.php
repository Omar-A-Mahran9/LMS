<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
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

        return [
               "id" => $this->id,
            'image' => $this->full_image_path,
            'title' => $this->title,
            'started_at' => $this->course->start_date,
            'quiz_required'=>$this->quiz_required,
            'is_enrolled' => $isEnrolled,

        ];
    }
}
