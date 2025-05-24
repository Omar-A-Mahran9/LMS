<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
public function validate(string $attribute, mixed $value, Closure $fail): void
{
    // $value = preg_replace('/[\s\-\(\)]+/', '', $value);

    // // Regex for international phone numbers:
    // // + followed by 7 to 15 digits (typical phone number length worldwide)
    // if (!preg_match('/^\+?[1-9]\d{6,14}$/', $value)) {
    //     $fail(__(":attribute") . ' ' . __('must be a valid international phone number.'));
    // }
}

}
