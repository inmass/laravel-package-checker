<?php

use Illuminate\Support\Facades\Route;
use Iinmass\LaravelPackageChecker\Http\Controllers\PackageCheckerController;

Route::get('/package-checker/list', [PackageCheckerController::class, 'listPackages'])->name('package-checker.list');
Route::get('/package-checker/get-installed-packages', [PackageCheckerController::class, 'getInstalledPackages'])->name('package-checker.get-installed-packages');
Route::get('/package-checker/get-package-details', [PackageCheckerController::class, 'getPackageDetails'])->name('package-checker.get-package-details');
Route::get('/package-checker/get-latest-version', [PackageCheckerController::class, 'getLatestVersion'])->name('package-checker.get-latest-version');
Route::post('/package-checker/get-size', [PackageCheckerController::class, 'getPackageSize'])->name('package-checker.get-size');
Route::get('/package-checker/get-vendor-size', [PackageCheckerController::class, 'getVendorSize'])->name('package-checker.get-vendor-size');
