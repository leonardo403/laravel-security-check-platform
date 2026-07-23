<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request, User $user)
    {
        $user = $request->user();

        $stats = [
            'total_scans' => $user->scans()->count(),
            'completed_scans' => $user->scans()->where('status', 'completed')->count(),
            'failed_scans' => $user->scans()->where('status', 'failed')->count(),
            'average_score' => $user->scans()
                ->whereHas('result')
                ->join('scan_results', 'scans.id', '=', 'scan_results.scan_id')
                ->avg('scan_results.score') ?? 0,
            'recent_scans' => $user->scans()
                ->with('result')
                ->latest()
                ->take(5)
                ->get(),
            'scans_by_status' => $user->scans()
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return view('dashboard', compact('stats'));
    }

    public function metrics()
    {
        // Endpoint para Prometheus
        $metrics = [];

        // Contador de scans
        $metrics[] = '# HELP app_scans_total Total number of scans';
        $metrics[] = '# TYPE app_scans_total counter';
        $metrics[] = 'app_scans_total ' . \App\Models\Scan::count();

        // Jobs na fila
        $queueSize = \Illuminate\Support\Facades\Queue::size();
        $metrics[] = '# HELP app_queue_size Number of jobs in queue';
        $metrics[] = '# TYPE app_queue_size gauge';
        $metrics[] = 'app_queue_size ' . $queueSize;

        return response(implode("\n", $metrics))
            ->header('Content-Type', 'text/plain');
    }
}
