<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Student;
use App\Rules\ExistButDeleted;
use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return abilities()->contains('create_students');
    }

    public function rules()
    {
       return [
        'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:512',

        'first_name' => ['required', 'string', 'max:255', new NotNumbersOnly()],
        'middle_name' => ['required', 'string', 'max:255', new NotNumbersOnly()],
        'last_name' => ['required', 'string', 'max:255', new NotNumbersOnly()],

        'email' => ['nullable', 'string', 'email:rfc,dns', 'unique:students,email'],
        'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20', 'unique:students,phone'],

        'parent_phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
        'parent_job' => ['required', 'string', 'max:255'],

        'gender' => ['required', 'in:male,female'],

        'government_id' => ['required', 'exists:governments,id'],
        'category_id' => ['required', 'exists:categories,id'],

        'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
        'password_confirmation' => ['required', 'same:password'],
    ];
    }
}
