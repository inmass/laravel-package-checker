<?php

use Illuminate\Support\Facades\Route;
use Iinmass\LaravelPackageChecker\Http\Controllers\PackageCheckerController;

Route::get('/package-checker/list', [PackageCheckerController::class, 'listPackages'])->name('package-checker.list');
Route::post('/package-checker/get-size', [PackageCheckerController::class, 'getPackageSize'])->name('package-checker.get-size');
Route::get('/package-checker/get-vendor-size', [PackageCheckerController::class, 'getVendorSize'])->name('package-checker.get-vendor-size');
