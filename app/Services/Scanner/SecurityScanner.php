<?php
namespace App\Services\Scanner;

use App\Models\Scan;
use App\Models\ScanResult;
use Illuminate\Support\Facades\Log;

class SecurityScanner
{
    public function scan(string $repositoryPath, Scan $scan): ScanResult
    {
        Log::info("Starting scan for: {$scan->repository_url}");

        $startTime = microtime(true);

        // Simular análise de vulnerabilidades
        $vulnerabilities = $this->analyzeVulnerabilities($repositoryPath);

        // Simular análise de dependências
        $dependencies = $this->analyzeDependencies($repositoryPath);

        $duration = microtime(true) - $startTime;

        $score = $this->calculateScore($vulnerabilities);

        Log::info("Scan completed with score: {$score}");

        return ScanResult::create([
            'scan_id' => $scan->id,
            'vulnerabilities' => $vulnerabilities,
            'dependencies' => $dependencies,
            'score' => $score,
            'duration_seconds' => round($duration, 2),
            'summary' => $this->generateSummary($vulnerabilities, $dependencies, $score),
        ]);
    }

    private function analyzeVulnerabilities(string $path): array
    {
        // Simulação - em produção usaria o pacote laravel-security-check
        $severities = ['critical', 'high', 'medium', 'low'];
        $vulnerabilities = [];

        for ($i = 0; $i < rand(1, 10); $i++) {
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

    private function analyzeDependencies(string $path): array
    {
        return [
            'total' => rand(20, 100),
            'outdated' => rand(0, 15),
            'vulnerable' => rand(0, 5),
            'packages' => [
                ['name' => 'laravel/framework', 'version' => '11.0', 'latest' => '11.5'],
                ['name' => 'guzzlehttp/guzzle', 'version' => '7.5', 'latest' => '7.8'],
                ['name' => 'monolog/monolog', 'version' => '3.2', 'latest' => '3.5'],
            ],
        ];
    }

    private function calculateScore(array $vulnerabilities): int
    {
        $weights = [
            'critical' => 10,
            'high' => 7,
            'medium' => 4,
            'low' => 1,
        ];

        $score = 100;
        foreach ($vulnerabilities as $vuln) {
            $score -= $weights[$vuln['severity']] ?? 0;
        }

        return max(0, $score);
    }

    private function generateSummary(array $vulnerabilities, array $dependencies, int $score): string
    {
        $critical = count(array_filter($vulnerabilities, fn($v) => $v['severity'] === 'critical'));
        $high = count(array_filter($vulnerabilities, fn($v) => $v['severity'] === 'high'));

        return "Security Score: {$score}/100. Found {$critical} critical and {$high} high severity vulnerabilities.";
    }
}
