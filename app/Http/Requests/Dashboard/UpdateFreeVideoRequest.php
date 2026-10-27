<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreeVideoRequest extends FormRequest
{
    public function authorize()
    {
        return abilities()->contains('update_free_videos');
    }

    public function rules()
    {
        return [
            // Optional: the YouTube thumbnail is used when there is none
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg,webp|max:2048',

            // Empty = all grades
            'category_id' => 'nullable|exists:categories,id',

            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',

            'video_url' => ['required', 'url', 'max:1000', 'regex:/(youtube\.com|youtu\.be)\//i'],
            'duration_minutes' => 'nullable|integer|min:0|max:1440',

            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'video_url.regex' => __('The video link must be a YouTube link'),
        ];
    }
}
