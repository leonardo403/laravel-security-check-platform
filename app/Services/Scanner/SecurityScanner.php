<?php

namespace App\Services\Scanner;

use App\Models\Scan;
use App\Models\ScanResult;
use Illuminate\Support\Facades\Log;

class SecurityScanner
{
    private array $modules;

    public function scan(string $repositoryPath, Scan $scan, ?callable $onProgress = null): ScanResult
    {
        Log::info("Starting scan for: {$scan->repository_url}", ['type' => $scan->scan_type]);

        $this->modules = $this->resolveModules($scan);

        $startTime = microtime(true);

        $this->updateProgress($onProgress, 30);

        $dependencies = $this->isEnabled(ScanModule::Dependencies)
            ? $this->analyzeDependencies($repositoryPath)
            : $this->skippedDependencies();

        $this->updateProgress($onProgress, 45);

        $vulnerabilities = $this->analyzeVulnerabilities($repositoryPath, $dependencies);
        $dependencies['vulnerable'] = $this->countVulnerableDependencies($vulnerabilities);

        $this->updateProgress($onProgress, 65);

        $configChecks = $this->isEnabled(ScanModule::Security)
            ? $this->analyzeConfig($repositoryPath)
            : [];

        $this->updateProgress($onProgress, 85);

        $duration = microtime(true) - $startTime;
        $score = $this->calculateScore($vulnerabilities, $configChecks);

        $this->updateProgress($onProgress, 95);

        Log::info("Scan completed with score: {$score}", ['modules' => $this->moduleValues()]);

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

    public function enabledModules(): array
    {
        return $this->modules;
    }

    private function resolveModules(Scan $scan): array
    {
        $values = $scan->modules;

        if (! is_array($values) || $values === []) {
            return ScanModule::cases();
        }

        $modules = [];

        foreach ($values as $value) {
            $module = ScanModule::tryFrom((string) $value);

            if ($module !== null) {
                $modules[] = $module;
            }
        }

        return $modules === [] ? ScanModule::cases() : $modules;
    }

    private function isEnabled(ScanModule $module): bool
    {
        return in_array($module, $this->modules, true);
    }

    private function moduleValues(): array
    {
        return array_map(fn (ScanModule $module) => $module->value, $this->modules);
    }

    private function skippedDependencies(): array
    {
        return [
            'total' => 0,
            'outdated' => 0,
            'vulnerable' => 0,
            'packages' => [],
            'skipped' => true,
        ];
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
        $dependencyVulnerabilities = [];

        if ($this->isEnabled(ScanModule::Dependencies) && ! empty($dependencies['packages'])) {
            $dependencyVulnerabilities = (new VulnerabilityChecker)->check($dependencies['packages']);
        }

        if (! $this->isEnabled(ScanModule::CodeQuality) && ! $this->isEnabled(ScanModule::Secrets)) {
            return $dependencyVulnerabilities;
        }

        $allowedCategories = [];

        if ($this->isEnabled(ScanModule::CodeQuality)) {
            $allowedCategories[] = CodePatternAnalyzer::CATEGORY_CODE_QUALITY;
        }

        if ($this->isEnabled(ScanModule::Secrets)) {
            $allowedCategories[] = CodePatternAnalyzer::CATEGORY_SECRETS;
        }

        $codeFindings = (new CodePatternAnalyzer)->analyze($path);

        $codeVulnerabilities = array_values(array_filter(
            $codeFindings,
            fn (array $finding) => in_array($finding['category'] ?? CodePatternAnalyzer::CATEGORY_CODE_QUALITY, $allowedCategories, true)
        ));

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

        $skipped = [];

        if (! $this->isEnabled(ScanModule::Security)) {
            $skipped[] = 'config checks';
        }

        if (! $this->isEnabled(ScanModule::Dependencies)) {
            $skipped[] = 'dependency analysis';
        }

        if (! $this->isEnabled(ScanModule::Secrets)) {
            $skipped[] = 'secrets detection';
        }

        if (! $this->isEnabled(ScanModule::CodeQuality)) {
            $skipped[] = 'code pattern analysis';
        }

        if ($skipped !== []) {
            $summary .= ' Not included in this scan: '.implode(', ', $skipped).'.';
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
