@extends('layouts.app')

@section('content')
@if(!$plan)
<div class="mb-6 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        </div>
        <div>
            <p class="font-medium text-amber-300 text-sm">{{ __('scans.no_active_plan') }}</p>
            <p class="text-xs text-amber-400/70">{{ __('scans.subscribe_to_continue') }}</p>
        </div>
    </div>
    <a href="{{ route('plans.index') }}" class="px-4 py-2 rounded-lg bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 transition text-sm font-medium whitespace-nowrap border border-amber-500/20">
        {{ __('scans.view_plans') }}
    </a>
</div>
@elseif($scansRemaining <= 0)
<div class="mb-6 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <div>
            <p class="font-medium text-rose-300 text-sm">{{ __('scans.scan_limit_reached') }}</p>
            <p class="text-xs text-rose-400/70">{{ __('scans.all_scans_used', ['max' => $plan->max_scans_per_month]) }}</p>
        </div>
    </div>
    <a href="{{ route('plans.index') }}" class="px-4 py-2 rounded-lg bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 transition text-sm font-medium whitespace-nowrap border border-rose-500/20">
        {{ __('scans.upgrade') }}
    </a>
</div>
@elseif($scansRemaining <= 3)
<div class="mb-6 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
    <p class="text-sm text-amber-300">{{ __('scans.few_scans_left', ['count' => $scansRemaining]) }}</p>
</div>
@endif

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">{{ __('scans.security_scans') }}</h1>
    <a href="{{ route('scans.create') }}"
        @if(!$plan || $scansRemaining <= 0)
        class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700/50 text-sm font-medium"
        @else
        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white text-sm font-semibold hover:from-teal-400 hover:to-teal-500 transition-all shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30"
        @endif>
        {{ __('scans.new_scan') }}
    </a>
</div>

@if($scans->count())
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl overflow-hidden backdrop-blur-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-slate-500 text-xs uppercase tracking-wider border-b border-slate-700/50">
                    <th class="px-6 py-3">{{ __('common.repository') }}</th>
                    <th class="px-6 py-3">{{ __('scans.type') }}</th>
                    <th class="px-6 py-3">{{ __('common.status') }}</th>
                    <th class="px-6 py-3">{{ __('common.score') }}</th>
                    <th class="px-6 py-3">{{ __('common.date') }}</th>
                    <th class="px-6 py-3 text-right">{{ __('scans.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @foreach($scans as $scan)
                <tr class="group hover:bg-slate-800/20 transition-colors {{ in_array($scan->status, ['pending', 'processing']) ? 'scan-active' : '' }}">
                    <td class="px-6 py-4">
                        <div class="truncate max-w-xs text-sm text-slate-300 group-hover:text-white transition-colors">{{ $scan->repository_url }}</div>
                        @if(in_array($scan->status, ['pending', 'processing']))
                        <div class="mt-2 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-teal-500 to-emerald-400 h-1.5 rounded-full transition-all duration-500 scan-progress-bar"
                                 style="width: {{ $scan->progress }}%"
                                 data-scan-id="{{ $scan->id }}"></div>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 scan-progress-text" data-scan-id="{{ $scan->id }}">
                            {{ $scan->progress }}%
                            @if($scan->status === 'pending')
                                - {{ __('scans.waiting_in_queue') }}
                            @elseif($scan->progress < 30)
                                - {{ __('scans.preparing') }}
                            @elseif($scan->progress < 50)
                                - {{ __('scans.analyzing_vulnerabilities') }}
                            @elseif($scan->progress < 65)
                                - {{ __('scans.analyzing_dependencies') }}
                            @elseif($scan->progress < 85)
                                - {{ __('scans.checking_configurations') }}
                            @elseif($scan->progress < 95)
                                - {{ __('scans.calculating_score') }}
                            @else
                                - {{ __('scans.generating_report') }}
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($scan->scan_type === 'repository')
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20">{{ __('scans.type_repository') }}</span>
                        @elseif($scan->scan_type === 'env')
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full bg-violet-500/10 text-violet-400 border border-violet-500/20">{{ __('scans.type_env') }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">{{ __('scans.type_upload') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full font-medium scan-status-badge"
                              data-scan-id="{{ $scan->id }}"
                              @if($scan->status === 'completed') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                              @elseif($scan->status === 'failed') bg-rose-500/10 text-rose-400 border border-rose-500/20
                              @elseif($scan->status === 'processing') bg-sky-500/10 text-sky-400 border border-sky-500/20
                              @else bg-amber-500/10 text-amber-400 border border-amber-500/20
                              @endif">
                            {{ trans()->has('scans.status_'.$scan->status) ? __('scans.status_'.$scan->status) : $scan->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($scan->result)
                            <span class="text-sm font-semibold
                                @if($scan->result->score >= 80) text-emerald-400
                                @elseif($scan->result->score >= 50) text-amber-400
                                @else text-rose-400
                                @endif">
                                {{ $scan->result->score }}%
                            </span>
                        @else
                            <span class="text-slate-600">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $scan->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('scans.show', $scan) }}" class="text-sm text-teal-400 hover:text-teal-300 font-medium transition-colors">{{ __('common.view') }}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $scans->withQueryString()->links() }}
</div>
@else
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-16 text-center backdrop-blur-sm">
    <div class="w-16 h-16 rounded-2xl bg-slate-800/50 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    <p class="text-slate-400 text-lg mb-4">{{ __('scans.no_scans') }}</p>
    <a href="{{ route('scans.create') }}"
        class="inline-flex items-center px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white text-sm font-semibold hover:from-teal-400 hover:to-teal-500 transition-all shadow-lg shadow-teal-500/20">
        {{ __('scans.create_first_scan') }}
    </a>
</div>
@endif

<script>
(function() {
    const statusMessages = {!! json_encode([
        5 => __('scans.preparing'),
        10 => __('scans.loading_source'),
        15 => __('scans.source_loaded'),
        20 => __('scans.analysis_started'),
        30 => __('scans.analyzing_dependencies'),
        45 => __('scans.analyzing_vulnerabilities'),
        65 => __('scans.checking_configurations'),
        85 => __('scans.calculating_score'),
        95 => __('scans.generating_report'),
        100 => __('scans.finished'),
    ]) !!};

    const messages = {
        preparing: '{{ __('scans.preparing') }}',
        analyzing_dependencies: '{{ __('scans.analyzing_dependencies') }}',
        analyzing_vulnerabilities: '{{ __('scans.analyzing_vulnerabilities') }}',
        checking_configurations: '{{ __('scans.checking_configurations') }}',
        calculating_score: '{{ __('scans.calculating_score') }}',
        generating_report: '{{ __('scans.generating_report') }}',
    };

    function getProgressLabel(progress) {
        if (progress < 30) return messages.preparing;
        if (progress < 45) return messages.analyzing_dependencies;
        if (progress < 65) return messages.analyzing_vulnerabilities;
        if (progress < 85) return messages.checking_configurations;
        if (progress < 95) return messages.calculating_score;
        return messages.generating_report;
    }

    const activeScanIds = [...document.querySelectorAll('.scan-progress-bar')]
        .map(el => el.dataset.scanId);

    if (activeScanIds.length === 0) return;

    function pollActiveScans() {
        const stillActive = [];

        activeScanIds.forEach(scanId => {
            fetch(`/scans/${scanId}/progress`)
                .then(r => r.json())
                .then(data => {
                    const bar = document.querySelector(`.scan-progress-bar[data-scan-id="${scanId}"]`);
                    const text = document.querySelector(`.scan-progress-text[data-scan-id="${scanId}"]`);
                    const badge = document.querySelector(`.scan-status-badge[data-scan-id="${scanId}"]`);

                    if (bar) bar.style.width = data.progress + '%';
                    if (text) text.textContent = data.progress + '% - ' + getProgressLabel(data.progress);

                    if (data.status === 'completed' && badge) {
                        badge.textContent = '{{ __('scans.status_completed') }}';
                        badge.className = 'inline-flex items-center px-2.5 py-0.5 text-xs rounded-full font-medium scan-status-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                        setTimeout(() => location.reload(), 1500);
                    } else if (data.status === 'failed' && badge) {
                        badge.textContent = '{{ __('scans.status_failed') }}';
                        badge.className = 'inline-flex items-center px-2.5 py-0.5 text-xs rounded-full font-medium scan-status-badge bg-rose-500/10 text-rose-400 border border-rose-500/20';
                        setTimeout(() => location.reload(), 1500);
                    } else if (data.progress < 100) {
                        stillActive.push(scanId);
                    }
                })
                .catch(() => {});
        });

        if (stillActive.length > 0) {
            setTimeout(pollActiveScans, 2000);
        }
    }

    pollActiveScans();
})();
</script>
@endsection
