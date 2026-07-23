<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

class StarterPasswordRules
{
    /** @return list<mixed> */
    public static function rules(): array
    {
        return ['required', 'string', Password::min(10)->mixedCase()->numbers()];
    }
}
