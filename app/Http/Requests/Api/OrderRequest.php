<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use App\Rules\PhoneNumber;
use App\Rules\NotNumbersOnly;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentStep = request()->route('step');

        $stepsRules = [
            1 => [
                'services'         => ['required', 'array', 'min:1'],
                'services.*.id'    => ['required', 'exists:addon_services,id'],
                'services.*.count' => ['required', 'integer', 'min:1'],
            ],

            2 => [
                'date'    => ['required', 'date'],
                'time'    => ['required', 'date_format:H:i'],
                'city_id' => ['required', 'exists:cities,id'],
                'address' => ['required', 'string'],
            ],
            3 => [
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
            ],

            4 => [
                'name'  => ['required', 'string', 'max:255', new NotNumbersOnly()],
                'phone' => [
                    'required',
                    'string',
                    'max:255',
                    new PhoneNumber(),
 
                ]
            ],
            5 => [
                'payment_type' => ['required', Rule::in(['cash', 'visa', 'wallet'])],
            ]
        ];


        return $stepsRules[$currentStep] ?? [];
    }
}
