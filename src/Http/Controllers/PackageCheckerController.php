<?php

namespace Iinmass\LaravelPackageChecker\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
USE Iinmass\LaravelPackageChecker\Classes\PackageChecker;

class PackageCheckerController extends BaseController
{
    public function listPackages()
    {
        return view('package-checker::list');
    }

    public function getInstalledPackages()
    {
        $installedPackages = PackageChecker::getInstalledPackages();
        return response()->json($installedPackages);
    }

    public function getPackageDetails()
    {
        $data = [
            'name' => request('name'),
            'version' => request('version'),
        ];

        $requirements = PackageChecker::getPackageDetailsFor($data);
        return response()->json($requirements);
    }

    public function getLatestVersion()
    {
        $name = request('name');
        $latestVersion = PackageChecker::getLatestVersion($name);
        return response()->json($latestVersion);
    }

    public function getPackageSize()
    {
        $name = request('name');
        $requirements = request('requirements') ?? [];
        if (!$name) {
            return response()->json(['error' => 'Invalid request']);
        }
        $packageSize = PackageChecker::getPackageSize($name, $requirements);
        return response()->json($packageSize);
    }

    public function getVendorSize()
    {
        $vendorSize = PackageChecker::getAllPackageSizes();
        $headOfResponse = [];
        $index = 0;
        foreach ($vendorSize as $package) {
            $headOfResponse[] = [
                'id' => 'ID_' . $index,
                'name' => '',
                'color' => '#' . substr(md5(rand()), 0, 6)
            ];
            $index++;
        }
        $vendorSize = collect($vendorSize)->map(function ($item, $key) {
            return [
                'name' => $item['name'],
                'parent' => 'ID_' . $key,
                'value' => (int)$item['size'],
            ];
        });
        $vendorSize = array_merge($headOfResponse, $vendorSize->toArray());
        return response()->json($vendorSize);
    }
}