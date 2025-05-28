<?php

namespace App\Http\Requests\Dashboard;

use App\Models\CourseVideo;
use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('update_videos');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
  $courseVideoId = request()->route('video');

    // Manually resolve the CourseVideo model
    $courseVideo = (new CourseVideo())->resolveRouteBinding($courseVideoId);
  return [
         'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:5120',
        'course_id' => 'required|exists:courses,id',
        'course_section_id' => 'nullable|exists:course_sections,id',

        'duration_seconds' => 'nullable|integer|min:0',
        'is_preview' => 'sometimes|boolean',
        'is_active' => 'sometimes|boolean',

            // Multilingual Titles and Descriptions
        'title_ar' => [
            'required',
            'max:255',
            new NotNumbersOnly(),
            Rule::unique('course_videos', 'title_ar')->ignore($courseVideo->id)
        ],
        'title_en' => [
            'required',
            'max:255',
            new NotNumbersOnly(),
            Rule::unique('course_videos', 'title_en')->ignore($courseVideo->id)
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
