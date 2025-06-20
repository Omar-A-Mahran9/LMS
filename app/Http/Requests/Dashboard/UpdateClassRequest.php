<?php

namespace App\Http\Requests\Dashboard;

use App\Models\CourseClass;
use App\Models\CourseVideo;
use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('update_classes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
  $sectionId = request()->route('section');

    // Manually resolve the CourseVideo model
  return [
        'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:5120',
        'attachment' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt|max:10240', // 10MB max

        'course_id' => 'required|exists:courses,id',
        'quiz_required' => 'sometimes|boolean',

        'course_section_id' => 'nullable|exists:course_sections,id',

        'is_preview' => 'sometimes|boolean',
        'is_active' => 'sometimes|boolean',

            // Multilingual Titles and Descriptions
        'title_ar' => [
            'required',
            'max:255',
            new NotNumbersOnly(),
            Rule::unique('classes', 'title_ar')->ignore($sectionId->id)
        ],
        'title_en' => [
            'required',
            'max:255',
            new NotNumbersOnly(),
            Rule::unique('classes', 'title_en')->ignore($sectionId->id)
        ],

    'description_ar' => ['required', new NotNumbersOnly()],
    'description_en' => ['required', new NotNumbersOnly()],


    // Video
  'video_url' => [
            'required',
            'regex:/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/'
        ],





];  }
}
