<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize()
    {
        return abilities()->contains('update_students');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $student = $this->route('student');

 return [
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'first_name' => ['required', 'string', 'max:255', new NotNumbersOnly()],
            'middle_name' => ['nullable', 'string', 'max:255', new NotNumbersOnly()],
            'last_name' => ['required', 'string', 'max:255', new NotNumbersOnly()],
            'gender' => ['required', 'in:male,female'],
            'government_id' => ['required', 'integer', 'exists:governments,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students')->ignore($student->id)],
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20', Rule::unique('students')->ignore($student->id)],
            'parent_phone' => ['nullable', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'parent_job' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
            'password_confirmation' => ['nullable', 'same:password'],
        ];
    }
}
