<?php

namespace App\Services\Scanner;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DependencyAnalyzer
{
    private const CACHE_TTL = 86400;

    public function analyze(string $path): array
    {
        $packages = $this->readComposer($path);
        $packages = array_merge($packages, $this->readNpm($path));

        $packages = array_values(collect($packages)
            ->unique('name')
            ->sortBy('name')
            ->all());

        $outdated = 0;

        foreach ($packages as &$package) {
            $latest = $this->resolveLatest($package['name'], $package['manager']);

            if ($latest !== null) {
                $package['latest'] = $latest;

                if ($latest !== $package['version']) {
                    $outdated++;
                }
            }
        }

        return [
            'total' => count($packages),
            'outdated' => $outdated,
            'vulnerable' => 0,
            'packages' => $packages,
        ];
    }

    private function readComposer(string $path): array
    {
        $lockPath = $path.'/composer.lock';

        if (File::exists($lockPath)) {
            $lock = json_decode(File::get($lockPath), true);

            return array_map(
                fn (array $pkg) => $this->package($pkg['name'], $pkg['version'], 'composer'),
                $lock['packages'] ?? []
            );
        }

        $composerPath = $path.'/composer.json';

        if (File::exists($composerPath)) {
            $composer = json_decode(File::get($composerPath), true);
            $requirements = array_merge(
                $composer['require'] ?? [],
                $composer['require-dev'] ?? []
            );

            return array_map(
                fn (string $name, string $version) => $this->package($name, $version, 'composer'),
                array_keys($requirements),
                array_values($requirements)
            );
        }

        return [];
    }

    private function readNpm(string $path): array
    {
        $lockPath = $path.'/package-lock.json';

        if (File::exists($lockPath)) {
            $lock = json_decode(File::get($lockPath), true);

            return array_map(
                fn (string $name, array $info) => $this->package($name, $info['version'] ?? 'unknown', 'npm'),
                array_keys($lock['dependencies'] ?? []),
                array_values($lock['dependencies'] ?? [])
            );
        }

        $packageJson = $path.'/package.json';

        if (File::exists($packageJson)) {
            $package = json_decode(File::get($packageJson), true);
            $requirements = array_merge(
                $package['dependencies'] ?? [],
                $package['devDependencies'] ?? []
            );

            return array_map(
                fn (string $name, string $version) => $this->package($name, $version, 'npm'),
                array_keys($requirements),
                array_values($requirements)
            );
        }

        return [];
    }

    private function package(string $name, string $version, string $manager): array
    {
        return [
            'name' => $name,
            'version' => ltrim(trim($version, ' ^~>=<'), 'v'),
            'latest' => null,
            'manager' => $manager,
        ];
    }

    private function resolveLatest(string $name, string $manager): ?string
    {
        $cacheKey = sha1($manager.'|'.$name);
        $cachePath = storage_path('app/cache/dependencies/'.$cacheKey.'.json');

        if (File::exists($cachePath)) {
            $cached = json_decode(File::get($cachePath), true);

            if (($cached['fetched_at'] ?? 0) + self::CACHE_TTL > time()) {
                return $cached['latest'];
            }
        }

        try {
            $latest = $manager === 'npm'
                ? $this->resolveNpmLatest($name)
                : $this->resolvePackagistLatest($name);
        } catch (\Throwable $e) {
            Log::warning("Failed to resolve latest version for {$name}", ['error' => $e->getMessage()]);

            return null;
        }

        if ($latest === null) {
            return null;
        }

        File::ensureDirectoryExists(dirname($cachePath));
        File::put($cachePath, json_encode([
            'fetched_at' => time(),
            'latest' => $latest,
        ]));

        return $latest;
    }

    private function resolvePackagistLatest(string $name): ?string
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get('https://repo.packagist.org/p2/'.$name.'.json');

        if (! $response->successful()) {
            return null;
        }

        $versions = $response->json('packages.'.$name) ?? [];

        $stable = [];
        foreach ($versions as $entry) {
            $version = ltrim($entry['version'] ?? '', 'v');

            if (preg_match('/^[\d.]+$/', $version)) {
                $stable[] = $version;
            }
        }

        if (empty($stable)) {
            return null;
        }

        usort($stable, fn (string $a, string $b) => version_compare($a, $b));

        return end($stable);
    }

    private function resolveNpmLatest(string $name): ?string
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get('https://registry.npmjs.org/'.str_replace('/', '%2F', $name).'/latest');

        if (! $response->successful()) {
            return null;
        }

        return $response->json('version');
    }
}
