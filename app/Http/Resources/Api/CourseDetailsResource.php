<?php

namespace App\Http\Resources\Api;

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


        return [
               "id" => $this->id,
            'image' => $this->full_image_path,
            'video_url' => base64_encode(convertToYoutubeEmbed($this->video_url)),

            'title' => $this->title,
            'description' => $this->description,
            'note' => $this->note,
            'phone' =>setting('sms_number'),

            'started_at' => $this->start_date,
            'count_video' => $this->count_video,
            'count_quiz' => $this->count_quiz,
            'count_homework' => $this->count_homework,
            'count_attachment' => $this->count_attachment, // only if you define this accessor



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
