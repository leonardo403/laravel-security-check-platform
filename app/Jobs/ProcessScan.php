// app/Jobs/ProcessScan.php
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
        Log::info("Processing scan job: {$this->scan->id}");

        $this->scan->update(['status' => 'processing']);

        try {
            // Simular clone do repositório
            $tempPath = storage_path('app/temp/' . uniqid('repo_'));
            mkdir($tempPath, 0755, true);

            // Executar scan
            $result = $scanner->scan($tempPath, $this->scan);

            // Atualizar status
            $this->scan->update([
                'status' => 'completed',
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
            // Limpar diretório temporário
            if (isset($tempPath) && is_dir($tempPath)) {
                rmdir($tempPath);
            }
        }
    }
}
