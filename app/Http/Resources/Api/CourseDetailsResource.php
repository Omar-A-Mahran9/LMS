<?php

namespace App\Http\Resources\Api;

use App\Models\CourseVideoStudent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    $studentId = auth('api')->id(); // or pass from $request
    $student = auth('api')->user(); // or pass from $request

    // Count how many videos the student has completed
        $completedCount = CourseVideoStudent::whereIn(
            'course_video_id',
            $this->videos->pluck('id')
        )->where('student_id', $studentId)
        ->where('is_completed', true)
        ->count();

        $isCompleted = $this->videos->count() > 0 && $completedCount === $this->videos->count();


        return [
            "id" => $this->id,
            'image' => $this->full_image_path,
            'video_url' => base64_encode(convertToYoutubeEmbed($this->video_url)),

            'title' => $this->title,
            'description' => $this->description,
            'note' => $this->note,
            'phone' =>setting('sms_number'),
            'is_class'=> $this->is_class,

            'started_at' => $this->start_date,
            'count_video' => $this->videos->count(),
            'count_quiz' => $this->quizzes->count(),
            'count_homework' => $this->homeworks->count(),
            'count_attachment' => $this->count_attachment, // only if you define this accessor
            'have_certificate' => $this->certificate_available,
           'is_completed' => $isCompleted,


                 'certificate_url' =>  $isCompleted &&  $this->certificate_available
                    ? getOrGeneratePublicCertificateUrl($student, $this)
                    : null,


            'price' => $this->is_free
                ? 'Free' // or 0 if you prefer numeric value
                : ($this->have_discount && $this->discount_percentage
                    ? round($this->price * (1 - $this->discount_percentage / 100), 2) // discounted price
                    : $this->price
                ),


            'original_price' => $this->price,
            'discount_percentage' => $this->have_discount ? $this->discount_percentage : null,
            'is_free' => $this->is_free,
            'have_discount' => $this->have_discount,
            'is_enrolled' => $this->is_enrolled,
            'payment_type' => $this->payment_type,

            'request_status' => [
                "key"=>$this->request_status,
                "value"=>__($this->request_status),
            ],

        ];
    }
}
