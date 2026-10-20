<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

/**
 * The one password rule of the panel: at least 8 characters with an uppercase letter,
 * a lowercase letter and a number. The strength bar in the user form shows the same checks.
 */
class PasswordPolicy
{
    public const MIN = 8;

    public static function rule(): Password
    {
        return Password::min(self::MIN)->mixedCase()->numbers();
    }
}
