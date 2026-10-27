<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Live;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('update_lives');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Optional: the class image is used when there is none
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg,webp|max:1024',

            'course_id' => 'required|exists:courses,id',
            // The class must belong to the chosen course
            'class_id' => [
                'nullable',
                Rule::exists('classes', 'id')->where('course_id', $this->input('course_id')),
            ],

            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',

            'platform' => ['required', Rule::in(Live::PLATFORMS)],
            // YouTube / Facebook live link, or the Zoom / Google Meet invite link
            'embed_url' => 'required|url|max:1000',
            'chat_enabled' => 'sometimes|boolean',
            'chat_embed_url' => 'nullable|url|max:1000',

            'stream_key' => 'nullable|string|max:255',
            'meeting_id' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',

            'start_time' => 'required|date',
            // Either the end time or the duration (60 minutes when both are empty)
            'end_time' => 'nullable|date|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1|max:1440',

            'is_recorded' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'class_id.exists' => __('The selected class does not belong to the selected course'),
            'end_time.after' => __('The end time must be after the start time'),
        ];
    }
}
