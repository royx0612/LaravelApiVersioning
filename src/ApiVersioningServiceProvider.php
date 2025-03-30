<?php

namespace Royx0612\LaravelApiVersioning;

use Illuminate\Support\ServiceProvider;
use Royx0612\LaravelApiVersioning\Console\Commands\MakeApiVersionedCommand;

class ApiVersioningServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeApiVersionedCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/config/versioned.php' => config_path('versioned.php'),
                __DIR__ . '/stubs' => base_path('stubs/vendor/laravel-api-versioning'),
            ], 'laravel-api-versioning');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/versioned.php',
            'versioned'
        );
    }
}
