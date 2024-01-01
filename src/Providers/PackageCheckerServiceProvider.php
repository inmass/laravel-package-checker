<?php

namespace Iinmass\LaravelPackageChecker\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class PackageCheckerServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('laravel-package-checker', function () {
            return new \Iinmass\LaravelPackageChecker\Http\Services\PackageCheckerService();
        });
    }


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->setPublishers();
        }
        
        $this->loadConfig();
        $this->loadRoutes();
        $this->loadViews();

    }

    public function setPublishers()
    {
        $this->publishes([
            __DIR__ . '/../config/package-checker.php' => config_path('package-checker.php')
        ], 'package-checker-config');
    }

    /**
     * Group the routes and set up configurations to load them.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        Route::group($this->routesConfigurations(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Routes configurations.
     *
     * @return array
     */
    private function routesConfigurations()
    {
        return [
            'prefix' => config('package-checker.routes.prefix'),
            'middleware' => config('package-checker.routes.middleware'),
            'namespace' => config('package-checker.routes.namespace'),
        ];
    }

    /**
     * Load views.
     *
     * @return void
     */
    protected function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'package-checker');
    }

    /**
     * Load config.
     *
     * @return array
     */
    protected function loadConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/package-checker.php', 'package-checker'
        );
    }
}
