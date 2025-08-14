<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreLiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('create_classes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
                        'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg,webp|max:1024',

            'course_id' => 'required|exists:courses,id',
            'class_id' => 'nullable|exists:classes,id',

            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',

            'platform' => 'required|in:zoom,youtube,twitch',
            'embed_url' => 'required|url',
   'chat_enabled' => 'sometimes|boolean',
'chat_embed_url' => 'required_if:chat_enabled,true|url',


            'stream_key' => 'nullable|string|max:255',
            'meeting_id' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',

            'start_time' => 'required|date',
            'end_time' => 'required|date',

            'duration_minutes' => 'nullable|integer|min:0',

            'is_recorded' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];

    }
}
