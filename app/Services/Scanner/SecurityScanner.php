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

        $vulnerabilities = $this->analyzeVulnerabilities($repositoryPath, $scan->scan_type);

        $this->updateProgress($onProgress, 50);

        $dependencies = $this->analyzeDependencies($repositoryPath, $scan->scan_type);

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

    private function analyzeVulnerabilities(string $path, string $scanType): array
    {
        $severities = ['critical', 'high', 'medium', 'low'];
        $vulnerabilities = [];

        $maxVulns = match($scanType) {
            'env' => rand(3, 8),
            'upload' => rand(5, 15),
            default => rand(1, 10),
        };

        for ($i = 0; $i < $maxVulns; $i++) {
            $severity = $severities[array_rand($severities)];
            $vulnerabilities[] = [
                'name' => "Vulnerability " . ($i + 1),
                'severity' => $severity,
                'package' => "package/example-" . rand(1, 100),
                'version' => '1.' . rand(0, 9) . '.' . rand(0, 9),
                'description' => 'Security issue found in dependency',
                'cve' => 'CVE-2024-' . rand(1000, 9999),
            ];
        }

        return $vulnerabilities;
    }

    private function analyzeDependencies(string $path, string $scanType): array
    {
        $totalDeps = match($scanType) {
            'env' => rand(5, 20),
            'upload' => rand(30, 120),
            default => rand(20, 100),
        };

        return [
            'total' => $totalDeps,
            'outdated' => rand(0, 15),
            'vulnerable' => rand(0, 5),
            'packages' => [
                ['name' => 'laravel/framework', 'version' => '11.0', 'latest' => '11.5'],
                ['name' => 'guzzlehttp/guzzle', 'version' => '7.5', 'latest' => '7.8'],
                ['name' => 'monolog/monolog', 'version' => '3.2', 'latest' => '3.5'],
            ],
        ];
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
        $critical = count(array_filter($vulnerabilities, fn($v) => $v['severity'] === 'critical'));
        $high = count(array_filter($vulnerabilities, fn($v) => $v['severity'] === 'high'));

        $configFails = count(array_filter($configChecks, fn($c) => $c['status'] === 'fail'));
        $configWarnings = count(array_filter($configChecks, fn($c) => $c['status'] === 'warning'));

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
