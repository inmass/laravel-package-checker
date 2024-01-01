<?php

use Illuminate\Support\Facades\Route;

Route::get('/list', 'PackageCheckerController@listPackages')->name('package-checker.list');
Route::get('/get-installed-packages', 'PackageCheckerController@getInstalledPackages')->name('package-checker.get-installed-packages');
Route::get('/get-package-details', 'PackageCheckerController@getPackageDetails')->name('package-checker.get-package-details');
Route::get('/get-latest-version', 'PackageCheckerController@getLatestVersion')->name('package-checker.get-latest-version');
Route::post('/get-size', 'PackageCheckerController@getPackageSize')->name('package-checker.get-size');
Route::get('/get-vendor-size', 'PackageCheckerController@getVendorSize')->name('package-checker.get-vendor-size');
