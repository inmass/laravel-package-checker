<?php

return [
    'routes' => [
        'prefix' => env('PACKAGE_CHECKER_ROUTES_PREFIX', 'package-checker'),
        'middleware' => env('PACKAGE_CHECKER_ROUTES_MIDDLEWARE', ['web','auth']),
        'namespace' => 'Iinmass\LaravelPackageChecker\Http\Controllers',
    ],
];