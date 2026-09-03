<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\ScanResult;
use App\Notifications\ScanNotification;
use App\Services\Scanner\SecurityScanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public function __construct(
        private Scan $scan
    ) {}

    public function handle(SecurityScanner $scanner): void
    {
        Log::info("Processing scan job: {$this->scan->id}", ['type' => $this->scan->scan_type]);

        $this->scan->update(['status' => 'processing', 'progress' => 5]);

        $this->notifyUser(ScanNotification::STATUS_CREATED);

        try {
            $tempPath = $this->prepareScanSource();

            $this->scan->update(['progress' => 20]);

            $result = $scanner->scan($tempPath, $this->scan, function ($progress) {
                $this->scan->update(['progress' => $progress]);
            });

            $this->scan->update([
                'status' => 'completed',
                'progress' => 100,
                'completed_at' => now(),
            ]);

            $this->notifyUser(ScanNotification::STATUS_COMPLETED, $result);

            Log::info("Scan completed successfully: {$this->scan->id}", [
                'score' => $result->score,
            ]);

        } catch (\Exception $e) {
            Log::error("Scan failed: {$this->scan->id}", [
                'error' => $e->getMessage(),
            ]);

            $this->scan->update(['status' => 'failed']);

            $this->notifyUser(ScanNotification::STATUS_FAILED, errorMessage: $e->getMessage());

            throw $e;
        } finally {
            if (isset($tempPath) && is_dir($tempPath)) {
                $this->cleanup($tempPath);
            }
        }
    }

    private function prepareScanSource(): string
    {
        $tempPath = Storage::disk('temp')->path('repo_'.uniqid());
        Storage::disk('temp')->makeDirectory($tempPath);

        switch ($this->scan->scan_type) {
            case 'repository':
                $this->scan->update(['progress' => 10]);
                $this->cloneRepository($this->scan->repository_url, $this->scan->branch ?? 'main', $tempPath);
                Log::info("Repository cloned for: {$this->scan->repository_url}");
                break;

            case 'env':
                $envContent = Storage::disk('local')->get($this->scan->env_file_path);
                file_put_contents($tempPath.'/.env', $envContent);
                $this->scan->update(['progress' => 15]);
                Log::info("Env file loaded to: {$tempPath}");
                break;

            case 'upload':
                $zipPath = Storage::disk('local')->path($this->scan->project_upload_path);
                $this->extractZip($zipPath, $tempPath);
                $this->scan->update(['progress' => 15]);
                Log::info("Project zip extracted to: {$tempPath}");
                break;
        }

        return $tempPath;
    }

    private function cloneRepository(string $url, string $branch, string $destPath): void
    {
        if (! preg_match('#^(https?://|git@|ssh://|git://)#', $url)) {
            throw new \RuntimeException("URL de repositório inválida: {$url}");
        }

        $command = sprintf(
            'timeout 120 git clone --depth 1 --single-branch --branch %s %s %s 2>&1',
            escapeshellarg($branch),
            escapeshellarg($url),
            escapeshellarg($destPath)
        );

        $output = [];
        $exitCode = 0;

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                'Falha ao clonar o repositório: '.implode(' ', array_slice($output, -5))
            );
        }
    }

    private function extractZip(string $zipPath, string $destPath): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($destPath);
            $zip->close();
        } else {
            throw new \RuntimeException('Failed to extract ZIP file');
        }
    }

    private function cleanup(string $path): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($path);
    }

    private function notifyUser(
        string $status,
        ?ScanResult $result = null,
        ?string $errorMessage = null,
    ): void {
        $user = $this->scan->user;

        if (! $user) {
            return;
        }

        $plan = $user->activeSubscription()?->plan;

        if (! $plan || ! ($plan->features['email_notifications'] ?? $plan->features['all_notifications'] ?? false)) {
            return;
        }

        $user->notify(new ScanNotification($this->scan, $status, $result, $errorMessage));
    }
}
