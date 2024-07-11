<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TitleValidationRule implements Rule
{
    public function passes($attribute, $value)
    {
        if (preg_match('/\s{2,}/', $value)) {
            return false;
        }

        // Check string length (adjust the min and max values as needed)
        if (strlen($value) < 3 || strlen($value) > 255) {
            return false;
        }

        // // Only allow alphabetic characters (no numbers or special characters)
        // if (!preg_match('/^[A-Za-z\s]+$/', $value)) {
        //     return false;
        // }

        return true;
    }

    public function message()
    {
        return 'This name foramt is wrong.';
    }
}
