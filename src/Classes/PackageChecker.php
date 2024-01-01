<?php

namespace Iinmass\LaravelPackageChecker\Classes;

use Illuminate\Support\Facades\Facade;

class PackageChecker extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'laravel-package-checker';
    }
}