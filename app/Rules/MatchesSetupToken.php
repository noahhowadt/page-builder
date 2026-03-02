<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MatchesSetupToken implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $expected = (string) env('SETUP_TOKEN');

        if (! hash_equals($expected, (string) $value)) {
            $fail('The provided setup token is invalid.');
        }
    }
}
