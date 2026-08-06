<?php

namespace App\Services\Scanner;

use App\Models\Scan;
use App\Models\ScanResult;
use Illuminate\Support\Facades\Log;

class SecurityScanner
{
    public function scan(string $repositoryPath, Scan $scan, ?callable $onProgress = null): ScanResult
    {
        Log::info("Starting scan for: {$scan->repository_url}", ['type' => $scan->scan_type]);

        $startTime = microtime(true);

        $this->updateProgress($onProgress, 30);

        $dependencies = $this->analyzeDependencies($repositoryPath);

        $this->updateProgress($onProgress, 45);

        $vulnerabilities = $this->analyzeVulnerabilities($repositoryPath, $dependencies);
        $dependencies['vulnerable'] = $this->countVulnerableDependencies($vulnerabilities);

        $this->updateProgress($onProgress, 65);

        $configChecks = $this->analyzeConfig($repositoryPath);

        $this->updateProgress($onProgress, 85);

        $duration = microtime(true) - $startTime;
        $score = $this->calculateScore($vulnerabilities, $configChecks);

        $this->updateProgress($onProgress, 95);

        Log::info("Scan completed with score: {$score}");

        return ScanResult::create([
            'scan_id' => $scan->id,
            'vulnerabilities' => $vulnerabilities,
            'dependencies' => $dependencies,
            'config_checks' => $configChecks,
            'score' => $score,
            'duration_seconds' => round($duration, 2),
            'summary' => $this->generateSummary($vulnerabilities, $dependencies, $configChecks, $score),
        ]);
    }

    private function analyzeConfig(string $path): array
    {
        $checker = new SecurityConfigChecker($path);

        return $checker->run();
    }

    private function analyzeDependencies(string $path): array
    {
        return (new DependencyAnalyzer)->analyze($path);
    }

    private function analyzeVulnerabilities(string $path, array $dependencies): array
    {
        $dependencyVulnerabilities = (new VulnerabilityChecker)->check($dependencies['packages'] ?? []);

        $codeVulnerabilities = (new CodePatternAnalyzer)->analyze($path);

        return array_merge($dependencyVulnerabilities, $codeVulnerabilities);
    }

    private function countVulnerableDependencies(array $vulnerabilities): int
    {
        $packages = [];

        foreach ($vulnerabilities as $vulnerability) {
            $package = $vulnerability['package'] ?? '';

            if ($package !== '' && $package !== 'source-code') {
                $packages[$package] = true;
            }
        }

        return count($packages);
    }

    private function calculateScore(array $vulnerabilities, array $configChecks): int
    {
        $weights = [
            'critical' => 10,
            'high' => 7,
            'medium' => 4,
            'low' => 1,
        ];

        $configPenalties = [
            'critical' => 15,
            'high' => 10,
            'medium' => 5,
            'low' => 2,
        ];

        $score = 100;

        foreach ($vulnerabilities as $vuln) {
            $score -= $weights[$vuln['severity']] ?? 0;
        }

        foreach ($configChecks as $check) {
            if ($check['status'] === 'fail') {
                $score -= $configPenalties[$check['severity']] ?? 5;
            } elseif ($check['status'] === 'warning') {
                $score -= ($configPenalties[$check['severity']] ?? 5) / 2;
            }
        }

        return max(0, min(100, (int) $score));
    }

    private function generateSummary(array $vulnerabilities, array $dependencies, array $configChecks, int $score): string
    {
        $critical = count(array_filter($vulnerabilities, fn ($v) => $v['severity'] === 'critical'));
        $high = count(array_filter($vulnerabilities, fn ($v) => $v['severity'] === 'high'));

        $configFails = count(array_filter($configChecks, fn ($c) => $c['status'] === 'fail'));
        $configWarnings = count(array_filter($configChecks, fn ($c) => $c['status'] === 'warning'));

        $summary = "Security Score: {$score}/100.";
        $summary .= " Found {$critical} critical and {$high} high severity vulnerabilities.";

        if ($configFails > 0 || $configWarnings > 0) {
            $summary .= " Config checks: {$configFails} failures, {$configWarnings} warnings.";
        }

        return $summary;
    }

    private function updateProgress(?callable $onProgress, int $progress): void
    {
        if ($onProgress) {
            $onProgress($progress);
        }
    }
}
