<?php

use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Platform\Providers\PlatformServiceProvider;
use Modules\User\Providers\UserServiceProvider;

return [
    PlatformServiceProvider::class,
    UserServiceProvider::class,
    AuthServiceProvider::class,
];
