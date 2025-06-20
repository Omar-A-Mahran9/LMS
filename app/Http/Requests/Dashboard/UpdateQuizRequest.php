<?php

namespace App\Http\Requests\Dashboard;

use App\Models\CourseVideo;
use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
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
  $quiz = request()->route('quiz');
     // Manually resolve the CourseVideo model
   return [
         'course_id' => 'nullable|exists:courses,id',
        'course_section_id' => 'nullable|exists:course_sections,id',
             'class_id' => 'nullable|exists:classes,id',
        'section_id' => 'nullable|exists:sections,id',

        'duration_minutes' => 'nullable|integer|min:0',
        'is_active' => 'sometimes|boolean',

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

    'description_ar' => ['required', new NotNumbersOnly()],
    'description_en' => ['required', new NotNumbersOnly()],




];  }
}
