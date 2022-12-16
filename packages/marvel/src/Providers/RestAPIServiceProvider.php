<?php

namespace Marvel\Providers;

use Illuminate\Support\ServiceProvider;


class RestApiServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutes();
    }

    public function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Rest/Routes.php');
    }
}
