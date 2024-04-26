<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Ita2CharsetRule implements ValidationRule
{
    // Define the ITA2 character set
    const ITA2_CHARACTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 .,?-!:()&;+/'\r\n";

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^['.preg_quote(self::ITA2_CHARACTERS, '/').']+$/', $value)) {
            $fail('The :attribute must contain only characters from the ITA2 character set.');
        }
    }
}
