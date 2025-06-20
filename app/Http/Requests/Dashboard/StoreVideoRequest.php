<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('create_videos');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
return [
    // Required image
        'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:5120',
        'course_id' => 'nullable|exists:courses,id',
        'class_id' => 'nullable|exists:classes,id',
        'section_id' => 'nullable|exists:sections,id',

        'course_section_id' => 'nullable|exists:course_sections,id',
        'title_ar' => 'required|string|max:255',
        'title_en' => 'required|string|max:255',
        'description_ar' => 'nullable|string',
        'description_en' => 'nullable|string',
        'video_url' => [
            'required',
            'regex:/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/'
        ],
        'duration_seconds' => 'nullable|integer|min:0',
        'is_preview' => 'sometimes|boolean',
        'is_active' => 'sometimes|boolean',
        'quiz_required' => 'sometimes|boolean',
        'quiz_id' => 'required_if:quiz_required,1|exists:quizzes,id',



];

    }
}
