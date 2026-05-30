<?php

namespace Modules\Shared\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

/**
 * Shared kernel: password validation policy.
 *
 * This trait lives in the Shared module because password policy is
 * cross-cutting — both the User context (confirming a password to delete an
 * account) and the Auth context (registration, reset, password change) depend
 * on it. Placing it in either module would create a User <-> Auth dependency
 * cycle, so the shared kernel owns it. The Shared module depends on nothing and
 * is depended on by everyone.
 */
trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
