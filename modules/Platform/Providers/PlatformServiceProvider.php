<?php

namespace Modules\Platform\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Modules\Platform\Console\Commands\MakeModuleCommand;
use Modules\Platform\Console\Commands\ModuleAwareControllerMakeCommand;
use Modules\Platform\Console\Commands\ModuleAwareObserverMakeCommand;
use Modules\Platform\Console\Commands\ModuleAwarePolicyMakeCommand;

/**
 * Service provider for the Platform module.
 *
 * Platform is the application shell — the host's own non-domain surface
 * (welcome page, dashboard frame, appearance toggle) plus app-wide defaults.
 * It is NOT a bounded context and owns no aggregate; it's where the routes,
 * global middleware and host configuration that used to sit loose under the
 * project root now live, behind a real module boundary. The console routes are
 * wired from bootstrap/app.php (the framework loads them in the console
 * context); this provider owns the web shell routes and the runtime defaults.
 */
class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
                // Replace the native generators whose interactive model picker
                // scans app/ so it scans modules/*/Models instead.
                ModuleAwareControllerMakeCommand::class,
                ModuleAwarePolicyMakeCommand::class,
                ModuleAwareObserverMakeCommand::class,
            ]);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
