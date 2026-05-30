<?php

namespace Modules\User\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\User\Events\UserRegistered;
use Modules\User\Listeners\LogRegisteredUser;

/**
 * Service provider for the User module.
 *
 * Each module owns and boots its own resources (migrations, routes, events,
 * bindings). Registered in bootstrap/providers.php. This is what makes a
 * module self-contained: drop the directory in, register the provider, done.
 */
class UserServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the module.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected array $listen = [
        UserRegistered::class => [
            LogRegisteredUser::class,
        ],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        });

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }
}
