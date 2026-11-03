<?php

namespace App\Rules;

use App\Support\RedirectPaths;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RedirectTarget implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || RedirectPaths::target($value) === null) {
            $fail('Choose a public path or an HTTPS URL on the canonical site. External and protected destinations are not allowed.');
        }
    }
}
