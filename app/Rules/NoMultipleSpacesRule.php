<?php

namespace App\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoMultipleSpacesRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (preg_match('/\s{2,}/', $value)) {
            $fail('The :attribute may only contain one space between words.');
        }
    }
}
