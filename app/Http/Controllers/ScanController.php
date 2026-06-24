<?php
namespace App\Http\Controllers;

use App\Models\Scan;
use App\Jobs\ProcessScan;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'repository_url' => 'required|url',
            'branch' => 'nullable|string|max:255',
        ]);

        // Verificar limite do plano
        $user = $request->user();
        $plan = $user->subscription?->plan;

        if ($plan) {
            $scansThisMonth = $user->scans()
                ->whereMonth('created_at', now()->month)
                ->count();

            if ($scansThisMonth >= $plan->max_scans_per_month) {
                return back()->with('error', 'Você atingiu o limite de scans do seu plano.');
            }
        }

        $scan = $user->scans()->create([
            'repository_url' => $validated['repository_url'],
            'branch' => $validated['branch'] ?? 'main',
            'status' => 'pending',
        ]);

        // Disparar job
        ProcessScan::dispatch($scan)->onQueue('scans');

        Log::info("Scan created: {$scan->id}");

        return redirect()
            ->route('scans.show', $scan)
            ->with('success', 'Scan iniciado com sucesso!');
    }

    public function show(Scan $scan)
    {
        $this->authorize('view', $scan);

        $scan->load('result');

        return view('scans.show', compact('scan'));
    }
}
