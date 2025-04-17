<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use App\Rules\PhoneNumber;
use App\Rules\NotNumbersOnly;
use App\Enums\PayingOffStatus;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequestt extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'services'         => ['required', 'array', 'min:1'],
            'services.*.id'    => ['required', 'exists:addon_services,id'],
            'services.*.count' => ['required', 'integer', 'min:1'],

            'date'             => ['required', 'date'],
            'time'             => ['required', 'date_format:H:i'],
            'city_id'          => ['required', 'exists:cities,id'],
            'address'          => ['required', 'string'],
            'name'             => ['required', 'string', 'max:255', new NotNumbersOnly()],
            'phone'            => ['required', 'string', 'max:255', new PhoneNumber()],
            'email'            => ['nullable', 'email', 'max:255'],
            'payment_type'     => ['nullable', 'in:cash,card'],
            'description'      => ['nullable', 'string']
        ];
    }
}
