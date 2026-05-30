<?php

namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\User\Events\UserRegistered;

/**
 * Example listener for the UserRegistered domain event.
 *
 * In a real application a listener like this would live in whichever module
 * owns the reaction — e.g. a Notification module sending a welcome email, or a
 * Billing module provisioning a trial. It is wired up in UserServiceProvider.
 * Implement Illuminate\Contracts\Queue\ShouldQueue to run it on the queue.
 */
class LogRegisteredUser
{
    public function handle(UserRegistered $event): void
    {
        Log::info('User registered', [
            'id' => $event->user->id,
            'email' => $event->user->email,
        ]);
    }
}
