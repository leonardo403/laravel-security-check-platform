<?php
namespace App\Http\Controllers;

use App\Models\Scan;
use App\Jobs\ProcessScan;
use App\Http\Requests\StoreScanRequest;
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

        return view('scans.index', compact('scans'));
    }

    public function create()
    {
        return view('scans.create');
    }

    public function store(StoreScanRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $scans = [];

        $hasRepo = !empty($validated['repository_url']);
        $hasEnv = $request->hasFile('env_file');
        $hasUpload = $request->hasFile('project_file');

        if (!$hasRepo && !$hasEnv && !$hasUpload) {
            return back()->with('error', 'Envie pelo menos uma fonte para scanear.');
        }

        $plan = $user->subscription?->plan;

        if ($plan) {
            $scansThisMonth = $user->scans()
                ->whereMonth('created_at', now()->month)
                ->count();

            $totalNew = ($hasRepo ? 1 : 0) + ($hasEnv ? 1 : 0) + ($hasUpload ? 1 : 0);

            if (($scansThisMonth + $totalNew) > $plan->max_scans_per_month) {
                return back()->with('error', 'Você atingiu o limite de scans do seu plano.');
            }
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
            $scans[] = $this->createScan($user, 'env', [
                'env_file_path' => $envPath,
                'repository_url' => 'env-upload://' . $envFile->getClientOriginalName(),
            ]);
        }

        if ($hasUpload) {
            $projectFile = $request->file('project_file');
            $projectPath = $projectFile->store('scans/uploads', 'local');
            $scans[] = $this->createScan($user, 'upload', [
                'project_upload_path' => $projectPath,
                'repository_url' => 'project-upload://' . $projectFile->getClientOriginalName(),
            ]);
        }

        foreach ($scans as $scan) {
            ProcessScan::dispatch($scan)->onQueue('scans');
            Log::info("Scan created: {$scan->id}", ['type' => $scan->scan_type]);
        }

        if (count($scans) === 1) {
            return redirect()
                ->route('scans.show', $scans[0])
                ->with('success', 'Scan iniciado com sucesso!');
        }

        return redirect()
            ->route('scans.index')
            ->with('success', count($scans) . ' scans iniciados com sucesso!');
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

    private function createScan($user, string $type, array $extra): Scan
    {
        return $user->scans()->create(array_merge([
            'scan_type' => $type,
            'status' => 'pending',
            'progress' => 0,
        ], $extra));
    }
}
