<?php

namespace App\Rules;

use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IranMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Phone::isValidIranMobile((string)$value)) {
            $fail("شماره موبایل نامعتبر است. نمونه صحیح: 09121234567");
        }
    }
}
