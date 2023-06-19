<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Illuminate\Support\ServiceProvider;
use Williamjulianvicary\LaravelJobResponse\Transport\TransportContract;

class LaravelJobResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('job-response.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'job-response');

        $this->app->singleton('laravel-job-response', fn () => new LaravelJobResponse());

        $this->app->singleton(TransportFactory::class, fn ($app) => new TransportFactory());

        $this->app->bind(TransportContract::class, fn ($app) => $app->make(TransportFactory::class)->getTransport(config('job-response.transport')));
    }
}
