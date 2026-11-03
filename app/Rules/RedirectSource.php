<?php

namespace App\Rules;

use App\Support\RedirectPaths;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RedirectSource implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! RedirectPaths::source($value)) {
            $fail('Use a literal public path beginning with one slash, without a query, fragment or protected route.');
        }
    }
}
