<?php

namespace App\Http\Resources\Api;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->user(); // authenticated student

        return [
            'first_name' => ['nullable', 'string', 'max:100', new NotNumbersOnly()],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100', new NotNumbersOnly()],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'parent_job' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female'],
            'government_id' => ['nullable', 'exists:governments,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => __('First Name'),
            'middle_name' => __('Middle Name'),
            'last_name' => __('Last Name'),
            'parent_phone' => __('Parent Phone'),
            'parent_job' => __('Parent Job'),
            'gender' => __('Gender'),
            'government_id' => __('Government'),
            'category_id' => __('Category'),
            'image' => __('Image'),
        ];
    }
}
