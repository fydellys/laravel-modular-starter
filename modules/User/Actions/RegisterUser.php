<?php

namespace Modules\User\Actions;

use Modules\User\Events\UserRegistered;
use Modules\User\Models\User;

/**
 * Use case: register a new user.
 *
 * This is the User context's command for creating its aggregate. It is the
 * single entry point other modules (e.g. Auth) call to bring a user into
 * existence, so the "create + announce" rule lives here and nowhere else.
 *
 * Note the two communication styles meeting in one place:
 *  - Inbound (sync): Auth calls this action directly because it needs the
 *    created User back. A command, not an event.
 *  - Outbound (async/fire-and-forget): we dispatch UserRegistered so any
 *    interested module can react. We do not call them; we announce.
 */
class RegisterUser
{
    public function handle(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        event(new UserRegistered($user));

        return $user;
    }
}
