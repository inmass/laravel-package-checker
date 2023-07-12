<?php

namespace Iinmass\LaravelPackageChecker\Http\Services;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PackageCheckerService {

    /**
     * Get the installed packages
     * 
     * @return array
     */
    public function getInstalledPackages(): array
    {
        $packages = [];

        // read the composer.json
        $composerJson = file_get_contents(base_path('composer.json'));

        // convert JSON to an associative array
        $composerConfig = json_decode($composerJson, true);

        // get the installed packages
        $installedPackages = $composerConfig['require'];

        // check if found and remove the php version
        if (isset($installedPackages['php'])) {
            unset($installedPackages['php']);
        }
        // remove php extensions
        foreach ($installedPackages as $key => $value) {
            if (str_contains($key, 'ext-')) {
                unset($installedPackages[$key]);
            }
        }

        // remove the "^" from the version number
        foreach ($installedPackages as $key => $value) {
            $installedPackages[$key] = str_replace('^', '', str_replace('v', '', $value));
            // append to the packages array
            $packages[] = [
                'name' => $key,
                'version' => $installedPackages[$key],
                'latest_version' => '',
                'status' => '',
                'release_date' => '',
                'requirements' => '',
                'size' => ''
            ];
        }

        // get the latest version of the packages
        $packages = $this->getLatestPackagesFor($packages);
       
        // get the status of the packages
        $packages = $this->getPackageDetailsFor($packages);

        return $packages;
    }

    /**
     * Get the latest version of the packages
     * 
     * @param array $installedPackages
     * @return array
     */

    private function getLatestPackagesFor(array $installedPackages): array
    {
        foreach ($installedPackages as $key => $value) {
            $installedPackages[$key]['latest_version'] = $this->getLatestVersion($value['name']);
        }

        return $installedPackages;
    }



    /**
     * Get package information from packagist.org
     * 
     * @param string $packageName
     * @return string
     */
    private function getPackageInfo(string $packageName): array|bool
    {
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get("https://packagist.org/packages/$packageName.json");
            return json_decode($response->getBody()->getContents(), true) ?? false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // means the package is not found
            return false;
        }
    }

    /**
     * Get the latest version of the package
     * 
     * @param string $packageName
     * @return string
     */
    private function getLatestVersion(string $packageName): string
    {
        $packageInfo = $this->getPackageInfo($packageName);

        if (!$packageInfo) {
            return 'Package not found';
        }

        // If default_branch is set, it is generally the latest stable version
        if (isset($packageInfo['package']['default_branch'])) {
            $latestVersion = $packageInfo['package']['default_branch'];
            // Ensure this branch version exists in versions list and doesn't contain "-dev"
            if (isset($packageInfo['package']['versions'][$latestVersion]) && strpos($latestVersion, '-dev') === false) {
                return  str_replace('v', '', $latestVersion);
            }
        }

        // Find the latest stable version manually, ignoring versions with "-dev", "-alpha", "-beta", or "-RC"
        foreach ($packageInfo['package']['versions'] as $version => $details) {
            if (!preg_match('/(dev|alpha|beta|RC)/', $version)) {
                // remove the "v" from the version number
                return str_replace('v', '', $version);
            }
        }
    }

    /**
     * Get the status of the packages
     * 
     * @param array $installedPackages
     * @return array
     */
    private function getPackageDetailsFor(array $installedPackages): array
    {
        foreach ($installedPackages as $key => $value) {
            // $installedPackages[$key]['status'] = $this->getVersionStatusAndReleaseDate($value['name'], $value['version']);
            $data = $this->getOtherPackageDetails($value['name'], $value['version']);
            $installedPackages[$key]['status'] = $data['status'];
            $installedPackages[$key]['release_date'] = $data['release_date'];
            $installedPackages[$key]['requirements'] = $data['requirements'];
        }

        return $installedPackages;
    }


    /**
     * Get the status of the package
     *
     * @param string $packageName
     * @param string $installedVersion
     * @return array
     */
    private function getOtherPackageDetails(string $packageName, string $installedVersion): array
    {
        $packageInfo = $this->getPackageInfo($packageName);

        if (!$packageInfo) {
            return $this->createPackageNotFoundResponse();
        }

        $desiredVersions = $this->filterDesiredVersions($packageInfo['package']['versions'], $installedVersion);

        if (empty($desiredVersions)) {
            return $this->createUnknownVersionResponse();
        }

        $desiredVersion = end($desiredVersions);
        $versionReleaseDate = new \DateTime($desiredVersion['time']);
        $status = $this->determineVersionStatus($versionReleaseDate);

        $versionRequirements = $this->extractVersionRequirements($desiredVersion['require']);

        return [
            'status' => $status,
            'release_date' => $versionReleaseDate->format('Y-m-d'),
            'requirements' => $versionRequirements
        ];
    }

    /**
     * Filter the desired versions based on the installed version
     *
     * @param array $versions
     * @param string $installedVersion
     * @return array
     */
    private function filterDesiredVersions(array $versions, string $installedVersion): array
    {
        return array_filter($versions, function ($versionData) use ($installedVersion) {
            if (isset($versionData['version']) && is_string($versionData['version'])) {
                $version = ltrim($versionData['version'], 'v');
                return preg_match("/^" . preg_quote($installedVersion, '/') . "/", $version);
            }
            return false;
        });
    }

    /**
     * Determine the status of the version based on the release date
     *
     * @param \DateTime $releaseDate
     * @return string
     */
    private function determineVersionStatus(\DateTime $releaseDate): string
    {
        $now = new \DateTime();
        $diff = $now->diff($releaseDate);

        if ($diff->y >= 2) {
            return 'worse';
        } elseif ($diff->y >= 1) {
            return 'bad';
        } else {
            return 'good';
        }
    }

    /**
     * Extract the version requirements, removing the PHP requirement if present
     *
     * @param array $requirements
     * @return array
     */
    private function extractVersionRequirements(array $requirements): array
    {
        if (isset($requirements['php'])) {
            unset($requirements['php']);
        }
        return array_keys($requirements);
    }

    /**
     * Create a response for the package not found scenario
     *
     * @return array
     */
    private function createPackageNotFoundResponse(): array
    {
        return [
            'status' => 'Package not found',
            'release_date' => '---',
            'requirements' => []
        ];
    }

    /**
     * Create a response for the unknown version scenario
     *
     * @return array
     */
    private function createUnknownVersionResponse(): array
    {
        return [
            'status' => 'Unknown version',
            'release_date' => '---',
            'requirements' => []
        ];
    }


    /**
     * Get the size of the packages
     * 
     * @param array $installedPackages
     * @return array
     */
    private function getPackageSizesFor(array $installedPackages): array
    {
        foreach ($installedPackages as $key => $value) {
            $installedPackages[$key]['size'] = $this->getPackageSize($value['name'], $value['requirements']);
        }

        return $installedPackages;
    }


    /**
     * Get the size of the package
     * 
     * @param string $packageName
     * @return string
     */
    public function getPackageSize(string $packageName, array $requirements): string
    {
        $packagePath = base_path("vendor/$packageName");

        if (!file_exists($packagePath)) {
            return '---';
        }

        $size = $this->getDirectorySize($packagePath);

        // if package has requirements
        if (!empty($requirements)) {
            foreach ($requirements as $requirement) {
                $requirementPath = base_path("vendor/$requirement");
                if (file_exists($requirementPath)) {
                    $size += $this->getDirectorySize($requirementPath) ?? 0;
                }
            }
        }

        // return $this->formatBytes($size);
        return self::formatBytes($size);
    }

    /**
     * Get the size of the directory
     * 
     * @param string $directory
     * @return string
     */
    private function getDirectorySize(string $directory): string
    {
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes(int $bytes): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the names and sizes of all packages in the vendor directory.
     *
     * @return array
     */
    public function getAllPackageSizes(): array
    {
        $packages = $this->getAllPackageNames();

        foreach ($packages as $key => $value) {
            $size = $this->getDirectorySize($value['path']);
            $packages[$key]['size'] = $size;
        }
        return $packages;
    }

    /**
     * Get the names of all packages in the vendor directory.
     * 
     * @return array
     */
    public function getAllPackageNames(): array
    {
        $installedPackagesFile = base_path('vendor/composer/installed.php');
        // get returned array from installed.php
        if (!file_exists($installedPackagesFile)) {
            return [];
        }
        $installedPackagesArray = (require $installedPackagesFile)['versions'] ?? [];

        foreach ($installedPackagesArray as $key => $value) {
            if (!isset($value['install_path'])) {
                continue;
            }
            if (strpos($value['install_path'], '/../../') !== false) {
                continue;
            }
            $packages[] = [
                'name' => $key,
                'path' => $value['install_path'],
            ];
        }

        return $packages;
    }



}