<?php

namespace Iinmass\LaravelPackageChecker\Providers;

use Illuminate\Support\ServiceProvider;

class PackageCheckerServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'package-checker');
    }

    public function register()
    {
        //
    }
}
