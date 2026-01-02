<?php

use Common\Core\Application;
use Common\Core\Middleware\BroadcastServiceProvider;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

return Application::create(
    basePath: dirname(__DIR__),
    providers: [
        \App\Providers\AppServiceProvider::class,
        \App\Providers\HorizonServiceProvider::class,
        //TelescopeApplicationServiceProvider::class,
    ],
);
