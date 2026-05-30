<?php

namespace Modules\User\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\User\Models\User;

/**
 * Domain event fired when a new user is registered.
 *
 * This is the public, cross-module signal of the User context. Other modules
 * react to it (welcome email, default profile, analytics, audit log) without
 * the User module ever knowing they exist. Keep the payload to identifiers and
 * the aggregate itself — never leak module-internal state through events.
 */
class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {}
}
