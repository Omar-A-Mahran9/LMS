<?php

namespace App\Http\Requests\Dashboard;

use App\Models\CourseVideo;
use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('update_quizzes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

   return [
        'class_id' => 'nullable|exists:classes,id',
        'section_id' => 'nullable|exists:sections,id',

        'course_id' => 'nullable|exists:courses,id',
        'course_section_id' => 'nullable|exists:course_sections,id',
        'attempt_count' => 'nullable|integer|min:0',

        'duration_minutes' => 'nullable|integer|min:0',
        'is_active' => 'sometimes|boolean',
        'have_reading_passages' => 'sometimes|boolean',

            // Multilingual Titles and Descriptions
        'title_ar' => [
            'required',
            'max:255',
            new NotNumbersOnly(),
        ],
        'title_en' => [
            'required',
            'max:255',
            new NotNumbersOnly(),
        ],

    'description_ar' => ['nullable', new NotNumbersOnly()],
    'description_en' => ['nullable', new NotNumbersOnly()],




];  }
}
