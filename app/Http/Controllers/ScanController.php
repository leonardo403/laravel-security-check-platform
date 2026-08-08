<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScanRequest;
use App\Jobs\ProcessScan;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $scans = $request->user()
            ->scans()
            ->with('result')
            ->latest()
            ->paginate(10);

        $user = $request->user();
        $plan = $user->activeSubscription()?->plan;
        $scansThisMonth = $user->scans()->whereMonth('created_at', now()->month)->count();
        $scansRemaining = $plan ? max(0, $plan->max_scans_per_month - $scansThisMonth) : 0;

        return view('scans.index', compact('scans', 'plan', 'scansRemaining'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $plan = $user->activeSubscription()?->plan;
        $scansThisMonth = $user->scans()->whereMonth('created_at', now()->month)->count();
        $scansRemaining = $plan ? max(0, $plan->max_scans_per_month - $scansThisMonth) : 0;

        return view('scans.create', compact('plan', 'scansThisMonth', 'scansRemaining'));
    }

    public function store(StoreScanRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $scans = [];
        $warning = null;

        $hasRepo = ! empty($validated['repository_url']);
        $hasEnv = $request->hasFile('env_file');
        $hasUpload = $request->hasFile('project_file');

        if (! $hasRepo && ! $hasEnv && ! $hasUpload) {
            return back()->with('error', 'Envie pelo menos uma fonte para scanear.');
        }

        $totalNew = ($hasRepo ? 1 : 0) + ($hasEnv ? 1 : 0) + ($hasUpload ? 1 : 0);

        $plan = $user->activeSubscription()?->plan;

        if (! $plan) {
            return back()->with('error', 'Você não possui um plano ativo. Assine um plano para realizar scans.');
        }

        $scansThisMonth = $user->scans()
            ->whereMonth('created_at', now()->month)
            ->count();

        $remaining = $plan->max_scans_per_month - $scansThisMonth;

        if ($remaining <= 0) {
            return back()->with('error', 'Você atingiu o limite de '.$plan->max_scans_per_month.' scans do seu plano este mês.');
        }

        if ($totalNew > $remaining) {
            return back()->with('error', 'Você tem apenas '.$remaining.' scan(s) restante(s) este mês. Tente enviar menos arquivos.');
        }

        if ($hasRepo) {
            $scans[] = $this->createScan($user, 'repository', [
                'repository_url' => $validated['repository_url'],
                'branch' => $validated['branch'] ?? 'main',
            ]);
        }

        if ($hasEnv) {
            $envFile = $request->file('env_file');
            $envPath = $envFile->store('scans/env', 'local');

            if ($this->envFileContainsKeys($envFile->get())) {
                $warning = 'O arquivo .env enviado contém chaves de configuração. Alguns antivírus e ferramentas de segurança bloqueiam uploads de arquivos com segredos (erro ERR_ACCESS_DENIED). Se o upload for bloqueado, envie o arquivo compactado em .zip pela opção "Upload Projeto".';
            }

            $scans[] = $this->createScan($user, 'env', [
                'env_file_path' => $envPath,
                'repository_url' => 'env-upload://'.$envFile->getClientOriginalName(),
            ]);
        }

        if ($hasUpload) {
            $projectFile = $request->file('project_file');
            $projectPath = $projectFile->store('scans/uploads', 'local');
            $scans[] = $this->createScan($user, 'upload', [
                'project_upload_path' => $projectPath,
                'repository_url' => 'project-upload://'.$projectFile->getClientOriginalName(),
            ]);
        }

        foreach ($scans as $scan) {
            ProcessScan::dispatch($scan)->onQueue('scans');
            Log::info("Scan created: {$scan->id}", ['type' => $scan->scan_type]);
        }

        if (count($scans) === 1) {
            $response = redirect()
                ->route('scans.show', $scans[0])
                ->with('success', 'Scan iniciado com sucesso!');
        } else {
            $response = redirect()
                ->route('scans.index')
                ->with('success', count($scans).' scans iniciados com sucesso!');
        }

        if ($warning) {
            $response->with('warning', $warning);
        }

        return $response;
    }

    public function show(Scan $scan)
    {
        $this->authorize('view', $scan);

        $scan->load('result');

        return view('scans.show', compact('scan'));
    }

    public function progress(Scan $scan)
    {
        $this->authorize('view', $scan);

        return response()->json([
            'progress' => $scan->progress,
            'status' => $scan->status,
        ]);
    }

    public function destroy(Scan $scan)
    {
        $this->authorize('delete', $scan);

        if ($scan->env_file_path && \Storage::disk('local')->exists($scan->env_file_path)) {
            \Storage::disk('local')->delete($scan->env_file_path);
        }

        if ($scan->project_upload_path && \Storage::disk('local')->exists($scan->project_upload_path)) {
            \Storage::disk('local')->delete($scan->project_upload_path);
        }

        $scan->delete();

        return redirect()
            ->route('scans.index')
            ->with('success', 'Scan removido com sucesso!');
    }

    private function createScan($user, string $type, array $extra): Scan
    {
        return $user->scans()->create(array_merge([
            'scan_type' => $type,
            'status' => 'pending',
            'progress' => 0,
        ], $extra));
    }

    private function envFileContainsKeys(string $content): bool
    {
        foreach (preg_split('/\R/', $content) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }

            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*\s*=\s*\S+/', $line)) {
                return true;
            }
        }

        return false;
    }
}
