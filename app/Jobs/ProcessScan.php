<?php
namespace App\Jobs;

use App\Models\Scan;
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

            Log::info("Scan completed successfully: {$this->scan->id}", [
                'score' => $result->score
            ]);

        } catch (\Exception $e) {
            Log::error("Scan failed: {$this->scan->id}", [
                'error' => $e->getMessage()
            ]);

            $this->scan->update(['status' => 'failed']);
            throw $e;
        } finally {
            if (isset($tempPath) && is_dir($tempPath)) {
                $this->cleanup($tempPath);
            }
        }
    }

    private function prepareScanSource(): string
    {
        $tempPath = storage_path('app/temp/' . uniqid('repo_'));
        mkdir($tempPath, 0755, true);

        switch ($this->scan->scan_type) {
            case 'repository':
                $this->scan->update(['progress' => 10]);
                Log::info("Simulating git clone for: {$this->scan->repository_url}");
                break;

            case 'env':
                $envContent = Storage::disk('local')->get($this->scan->env_file_path);
                file_put_contents($tempPath . '/.env', $envContent);
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

    private function extractZip(string $zipPath, string $destPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($destPath);
            $zip->close();
        } else {
            throw new \RuntimeException("Failed to extract ZIP file");
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
}
