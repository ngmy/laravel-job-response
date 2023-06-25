<?php

declare(strict_types=1);

namespace Williamjulianvicary\LaravelJobResponse;

use Illuminate\Bus\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
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

        $this->app->singleton('laravel-job-response', fn (Application $app): LaravelJobResponse => new LaravelJobResponse(
            $app->make(Dispatcher::class),
            $app->make(TransportContract::class),
        ));

        $this->app->singleton(TransportFactory::class, fn (Application $app): TransportFactory => new TransportFactory());

        $this->app->bind(TransportContract::class, function (Application $app): TransportContract {
            \assert(\in_array(Config::get('job-response.transport'), TransportFactory::TRANSPORT_TYPES, true));

            return $app->make(TransportFactory::class)->getTransport(Config::get('job-response.transport'));
        });
    }
}
