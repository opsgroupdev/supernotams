<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use function PHPUnit\Framework\stringContains;

class IsAtcFlightPlan implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! str_contains($value, 'DOF/') || ! str_contains($value, 'REG/')) {
            $fail("Hey, it's not you, it's me. I just don't think this is a valid ATC flight plan! - Would you mind trying some different text and have another go?");
        }
    }
}
