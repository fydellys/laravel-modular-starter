<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Modules\Shared\Concerns\PasswordValidationRules;
use Modules\User\Actions\RegisterUser;
use Modules\User\Concerns\ProfileValidationRules;
use Modules\User\Models\User;

/**
 * Fortify adapter for user registration.
 *
 * Auth's only job here is to satisfy Fortify's contract: validate the incoming
 * request, then hand off to the User context. Creating the user (and announcing
 * it via the UserRegistered event) is User-domain logic, so we delegate to
 * RegisterUser rather than touching the model directly. Dependency flows one
 * way only: Auth -> User, never the reverse.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly RegisterUser $registerUser,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return $this->registerUser->handle(
            name: $input['name'],
            email: $input['email'],
            password: $input['password'],
        );
    }
}
