<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('create_courses');
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
    'slide_image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:5120',

    // Multilingual Titles and Descriptions
    'title_ar' => ['required', 'max:255', new NotNumbersOnly()],
    'title_en' => ['required', 'max:255', new NotNumbersOnly(), 'unique:courses,title_en'],

    'description_ar' => ['required', new NotNumbersOnly()],
    'description_en' => ['required', new NotNumbersOnly()],

    // Notes
    'note_ar' => ['required', 'string'],
    'note_en' => ['required', 'string'],

    // Video
    'video_url' => ['required', 'url'],

    // SEO
    'slug' => ['nullable', 'alpha_dash', 'unique:courses,slug'],
    'meta_title' => ['nullable', 'string', 'max:255'],
    'meta_description' => ['nullable', 'string'],

    // Pricing
    'price' => ['nullable', 'numeric', 'min:0'],
    'is_free' => ['nullable', 'boolean'],
    'is_active' => ['nullable', 'boolean'],

    // Level & Status
    'level' => ['required', 'in:beginner,intermediate,advanced'],
    'status' => ['required', 'in:draft,published,archived'],

    // Certificate
    'certificate_available' => ['nullable', 'boolean'],

    // Duration & Dates
    'duration_minutes' => ['nullable', 'integer', 'min:1'],
    'start_date' => ['nullable', 'date'],
    'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
    'start_time' => ['nullable', 'date_format:H:i'],
    'end_time' => ['nullable', 'date_format:H:i'],

    // Enrollment
    'max_students' => ['nullable', 'integer', 'min:1'],
    'is_enrollment_open' => ['nullable', 'boolean'],

    // Zoom
    'zoom_link' => ['nullable', 'url'],
    'zoom_meeting_id' => ['nullable', 'string'],
    'zoom_password' => ['nullable', 'string'],

    // Relationships
    'instructor_id' => ['nullable', 'exists:users,id'],
    'category_id' => ['nullable', 'exists:categories,id'],

    // Flags
    'show_in_home' => ['nullable', 'boolean'],
    'featured' => ['nullable', 'boolean'],
];

    }
}
