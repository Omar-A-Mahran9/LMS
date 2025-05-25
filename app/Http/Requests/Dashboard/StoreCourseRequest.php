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
    'slide_image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:5120',

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

   // Relationships
    'instructor_id' => ['required', 'exists:admins,id'],
    'category_id' => ['required', 'exists:categories,id'],


    // Pricing
    'price' => ['required_unless:is_free,1', 'numeric', 'min:1'],
    'is_free' => ['nullable', 'boolean'],

    // Discount
    'have_discount' => ['nullable', 'boolean'],
    'discount_percentage' => [
        'required_if:have_discount,1',
        'nullable',
        'integer',
        'min:1',
        'max:100',
    ],


    'is_active' => ['nullable', 'boolean'],

    // Level & Status
    'level' => ['nullable', 'in:beginner,intermediate,advanced'],
    'status' => ['nullable', 'in:draft,published,archived'],

    // Certificate
    'certificate_available' => ['nullable', 'boolean'],

    // Duration & Dates
    'start_date' => ['required', 'date', 'after_or_equal:today'],
    'end_date' => ['required', 'date', 'after_or_equal:start_date'],


    // Enrollment
    'max_students' => ['nullable', 'integer', 'min:1'],
    'is_enrollment_open' => ['nullable', 'boolean'],



    // Flags
    'show_in_home' => ['nullable', 'boolean'],
    'featured' => ['nullable', 'boolean'],
];

    }
}
