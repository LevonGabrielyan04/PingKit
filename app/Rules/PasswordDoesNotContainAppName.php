<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class PasswordDoesNotContainAppName implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $appName = config('app.name');

        if (! is_string($appName) || $appName === '') {
            return;
        }

        if (Str::contains(Str::lower($value), Str::lower($appName))) {
            $fail('The :attribute must not contain the application name.');
        }
    }
}
