@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('scans.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ __('scans.back') }}
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('common.repository') }}</span>
        <p class="text-lg font-bold text-white mt-1 truncate">{{ $scan->repository_url }}</p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('scans.scan_type') }}</span>
        <p class="text-lg font-bold text-white mt-1">
            @if($scan->scan_type === 'repository')
                {{ __('scans.repository_link') }}
            @elseif($scan->scan_type === 'env')
                {{ __('scans.upload_env') }}
            @else
                {{ __('scans.upload_project') }}
            @endif
        </p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('common.status') }}</span>
        <div class="mt-1">
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full font-medium
                @if($scan->status === 'completed') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                @elseif($scan->status === 'failed') bg-rose-500/10 text-rose-400 border border-rose-500/20
                @else bg-amber-500/10 text-amber-400 border border-amber-500/20
                @endif">
                {{ trans()->has('scans.status_'.$scan->status) ? __('scans.status_'.$scan->status) : $scan->status }}
            </span>
        </div>
    </div>
</div>

@if(in_array($scan->status, ['pending', 'processing']))
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 mb-6 backdrop-blur-sm" id="progress-container">
    <h3 class="text-slate-200 font-medium mb-4">{{ __('scans.scan_progress') }}</h3>
    <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden">
        <div id="progress-bar" class="bg-gradient-to-r from-teal-500 to-emerald-400 h-3 rounded-full transition-all duration-500"
             style="width: {{ $scan->progress }}%"></div>
    </div>
    <p class="text-sm text-slate-400 mt-3">
        <span id="progress-text" class="font-medium text-white">{{ $scan->progress }}%</span> —
        <span id="progress-status">
            @if($scan->status === 'pending')
                {{ __('scans.waiting_to_start') }}
            @else
                {{ __('scans.processing') }}
            @endif
        </span>
    </p>
</div>
@endif

@if($scan->result)
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('common.score') }}</span>
        <p class="text-3xl font-bold mt-1
            @if($scan->result->score >= 80) text-emerald-400
            @elseif($scan->result->score >= 50) text-amber-400
            @else text-rose-400
            @endif">
            {{ $scan->result->score }}%
        </p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('scans.duration') }}</span>
        <p class="text-3xl font-bold text-white mt-1">{{ $scan->result->duration_seconds }}s</p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('scans.dependencies') }}</span>
        <p class="text-3xl font-bold text-white mt-1">{{ $scan->result->dependencies['total'] ?? '-' }}</p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm">
        <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('scans.outdated') }}</span>
        <p class="text-3xl font-bold text-amber-400 mt-1">{{ $scan->result->dependencies['outdated'] ?? '-' }}</p>
    </div>
</div>

@if(!empty($scan->result->vulnerabilities))
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 mb-6 backdrop-blur-sm">
    <h2 class="text-lg font-bold text-white mb-4">{{ __('scans.vulnerabilities') }} ({{ count($scan->result->vulnerabilities) }})</h2>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-slate-500 text-xs uppercase tracking-wider border-b border-slate-700/50">
                    <th class="px-4 py-2">{{ __('common.name') }}</th>
                    <th class="px-4 py-2">{{ __('scans.severity') }}</th>
                    <th class="px-4 py-2">{{ __('scans.package') }}</th>
                    <th class="px-4 py-2">CVE</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @foreach($scan->result->vulnerabilities as $vuln)
                <tr class="group">
                    <td class="px-4 py-3 text-sm text-slate-300 group-hover:text-white transition-colors">{{ $vuln['name'] ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @php $severity = $vuln['severity'] ?? 'unknown'; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full font-medium
                            @if($severity === 'critical') bg-rose-500/10 text-rose-400 border border-rose-500/20
                            @elseif($severity === 'high') bg-orange-500/10 text-orange-400 border border-orange-500/20
                            @elseif($severity === 'medium') bg-amber-500/10 text-amber-400 border border-amber-500/20
                            @else bg-sky-500/10 text-sky-400 border border-sky-500/20
                            @endif">
                            {{ trans()->has('scans.severity_'.$severity) ? __('scans.severity_'.$severity) : $severity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-400">{{ $vuln['package'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-400">{{ $vuln['cve'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(!empty($scan->result->config_checks))
@php
    $configFails = collect($scan->result->config_checks)->where('status', 'fail');
    $configWarnings = collect($scan->result->config_checks)->where('status', 'warning');
    $configPasses = collect($scan->result->config_checks)->where('status', 'pass');
@endphp
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 mb-6 backdrop-blur-sm">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-white">{{ __('scans.security_checks') }} ({{ count($scan->result->config_checks) }})</h2>
        <div class="flex gap-3 text-sm">
            @if($configFails->count() > 0)
                <span class="text-rose-400 font-medium">{{ __('scans.failures', ['count' => $configFails->count()]) }}</span>
            @endif
            @if($configWarnings->count() > 0)
                <span class="text-amber-400 font-medium">{{ __('scans.warnings', ['count' => $configWarnings->count()]) }}</span>
            @endif
            @if($configPasses->count() > 0)
                <span class="text-emerald-400 font-medium">{{ __('scans.ok', ['count' => $configPasses->count()]) }}</span>
            @endif
        </div>
    </div>
    <div class="space-y-2">
        @foreach($scan->result->config_checks as $check)
        <div class="flex items-start gap-3 p-3 rounded-xl
            @if($check['status'] === 'fail') bg-rose-500/5 border border-rose-500/10
            @elseif($check['status'] === 'warning') bg-amber-500/5 border border-amber-500/10
            @else bg-emerald-500/5 border border-emerald-500/10
            @endif">
            <div class="mt-0.5 flex-shrink-0">
                @if($check['status'] === 'fail')
                    <div class="w-5 h-5 rounded-full bg-rose-500/20 flex items-center justify-center">
                        <svg class="w-3 h-3 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                @elseif($check['status'] === 'warning')
                    <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-3 h-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                @else
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-slate-200 text-sm">{{ $check['name'] }}</span>
                    @if($check['severity'] !== 'info')
                        @php $severity = $check['severity']; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium
                            @if($severity === 'critical') bg-rose-500/10 text-rose-400 border border-rose-500/20
                            @elseif($severity === 'high') bg-orange-500/10 text-orange-400 border border-orange-500/20
                            @elseif($severity === 'medium') bg-amber-500/10 text-amber-400 border border-amber-500/20
                            @else bg-sky-500/10 text-sky-400 border border-sky-500/20
                            @endif">
                            {{ trans()->has('scans.severity_'.$severity) ? __('scans.severity_'.$severity) : $severity }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-400 mt-1">{{ $check['message'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(!empty($scan->result->dependencies['packages']))
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 mb-6 backdrop-blur-sm">
    <h2 class="text-lg font-bold text-white mb-4">{{ __('scans.dependencies') }}</h2>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-slate-500 text-xs uppercase tracking-wider border-b border-slate-700/50">
                    <th class="px-4 py-2">{{ __('scans.package') }}</th>
                    <th class="px-4 py-2">{{ __('scans.version') }}</th>
                    <th class="px-4 py-2">{{ __('scans.latest') }}</th>
                    <th class="px-4 py-2">{{ __('common.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @foreach($scan->result->dependencies['packages'] as $pkg)
                <tr class="group">
                    <td class="px-4 py-3 text-sm text-slate-300 group-hover:text-white transition-colors">{{ $pkg['name'] }}</td>
                    <td class="px-4 py-3 text-sm text-slate-400">{{ $pkg['version'] }}</td>
                    <td class="px-4 py-3 text-sm text-slate-400">{{ $pkg['latest'] ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if(empty($pkg['latest']) || $pkg['version'] === $pkg['latest'])
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">{{ __('scans.up_to_date') }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">{{ __('scans.outdated_dependency') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm">
    <h2 class="text-lg font-bold text-white mb-2">{{ __('scans.summary') }}</h2>
    <p class="text-slate-400 text-sm leading-relaxed">{{ $scan->result->summary }}</p>
</div>
@else
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-16 text-center backdrop-blur-sm" id="waiting-container">
    <div class="w-16 h-16 rounded-2xl bg-slate-800/50 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-600 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    <p class="text-slate-400 text-lg">{{ __('scans.waiting_result') }}</p>
</div>
@endif

@if(in_array($scan->status, ['pending', 'processing']))
<script>
(function() {
    const scanId = {{ $scan->id }};
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressStatus = document.getElementById('progress-status');
    const progressContainer = document.getElementById('progress-container');
    const waitingContainer = document.getElementById('waiting-container');

    const statusMessages = {
        5: '{{ __('scans.preparing') }}',
        10: '{{ __('scans.loading_source') }}',
        15: '{{ __('scans.source_loaded') }}',
        20: '{{ __('scans.analysis_started') }}',
        30: '{{ __('scans.analyzing_dependencies') }}',
        45: '{{ __('scans.analyzing_vulnerabilities') }}',
        65: '{{ __('scans.checking_configurations') }}',
        85: '{{ __('scans.calculating_score') }}',
        95: '{{ __('scans.generating_report') }}',
        100: '{{ __('scans.finished') }}',
    };

    const processingLabel = '{{ __('scans.processing') }}';

    function pollProgress() {
        fetch(`/scans/${scanId}/progress`)
            .then(response => response.json())
            .then(data => {
                progressBar.style.width = data.progress + '%';
                progressText.textContent = data.progress + '%';
                progressStatus.textContent = statusMessages[data.progress] || processingLabel;

                if (data.progress < 100 && data.status !== 'failed') {
                    setTimeout(pollProgress, 2000);
                } else {
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => {
                setTimeout(pollProgress, 3000);
            });
    }

    pollProgress();
})();
</script>
@endif
@endsection
