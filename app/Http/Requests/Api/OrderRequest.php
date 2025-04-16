<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use App\Rules\PhoneNumber;
use App\Rules\NotNumbersOnly;
use App\Enums\PayingOffStatus;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        $currentStep = request()->route('step');

        $stepsRules = [
            1 => [
                'addon_service_id' => ['required', 'exists:addon_services,id'],
            ],
            2 => [
                'count' => ['required', 'integer', 'min:1'],
            ],
            3 => [
                'date' => ['required', 'date'],
                'time' => ['required', 'date_format:H:i'],
                'city_id' => ['required', 'exists:cities,id'],
                'address' => ['required', 'string'],
            ],
            4 => [
                'name' => ['required', 'string', 'max:255', new NotNumbersOnly()],
                'phone' => [
                    'required',
                    'string',
                    'max:255',
                    new PhoneNumber(),
                    function ($attribute, $value, $fail) {
                        $customer = Customer::where('phone', $value)->first();
                        if ($customer) {
                            $fail(__('Phone number already registered.'));
                        }
                    }
                ]
            ],
            5 => [
                'otp' => ['required', 'string', 'size:6'], // assuming OTP is 6 digits
            ]
        ];

        return $stepsRules[$currentStep] ?? [];
    }
}
