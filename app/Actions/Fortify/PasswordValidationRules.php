<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{

    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array
     */

    protected function passwordRules()
    {
        return ['required', 'string', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).{8,16}$/', 'confirmed'];
    }

}
