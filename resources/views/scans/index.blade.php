@extends('layouts.app')

@section('content')
@if(!$plan)
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-medium">{{ __('scans.no_active_plan') }}</p>
            <p class="text-sm">{{ __('scans.subscribe_to_continue') }}</p>
        </div>
        <a href="{{ route('plans.index') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm whitespace-nowrap">
            {{ __('scans.view_plans') }}
        </a>
    </div>
</div>
@elseif($scansRemaining <= 0)
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-medium">{{ __('scans.scan_limit_reached') }}</p>
            <p class="text-sm">{{ __('scans.all_scans_used', ['max' => $plan->max_scans_per_month]) }}</p>
        </div>
        <a href="{{ route('plans.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm whitespace-nowrap">
            {{ __('scans.upgrade') }}
        </a>
    </div>
</div>
@elseif($scansRemaining <= 3)
<div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded mb-4">
    <p class="font-medium">{{ __('scans.few_scans_left', ['count' => $scansRemaining]) }}</p>
</div>
@endif

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">{{ __('scans.security_scans') }}</h1>
    <a href="{{ route('scans.create') }}"
        @if(!$plan || $scansRemaining <= 0)
        class="bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed"
        @else
        class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition"
        @endif>
        {{ __('scans.new_scan') }}
    </a>
</div>

@if($scans->count())
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500 bg-gray-50">
                <th class="px-6 py-3">{{ __('common.repository') }}</th>
                <th class="px-6 py-3">{{ __('scans.type') }}</th>
                <th class="px-6 py-3">{{ __('common.status') }}</th>
                <th class="px-6 py-3">{{ __('common.score') }}</th>
                <th class="px-6 py-3">{{ __('common.date') }}</th>
                <th class="px-6 py-3 text-right">{{ __('scans.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scans as $scan)
            <tr class="border-t hover:bg-gray-50 {{ in_array($scan->status, ['pending', 'processing']) ? 'scan-active' : '' }}">
                <td class="px-6 py-4">
                    <div class="truncate max-w-xs">{{ $scan->repository_url }}</div>
                    @if(in_array($scan->status, ['pending', 'processing']))
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500 scan-progress-bar"
                             style="width: {{ $scan->progress }}%"
                             data-scan-id="{{ $scan->id }}"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 scan-progress-text" data-scan-id="{{ $scan->id }}">
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
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">{{ __('scans.type_repository') }}</span>
                    @elseif($scan->scan_type === 'env')
                        <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">{{ __('scans.type_env') }}</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-800">{{ __('scans.type_upload') }}</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded scan-status-badge"
                          data-scan-id="{{ $scan->id }}"
                          @if($scan->status === 'completed') bg-green-100 text-green-800
                          @elseif($scan->status === 'failed') bg-red-100 text-red-800
                          @elseif($scan->status === 'processing') bg-blue-100 text-blue-800
                          @else bg-yellow-100 text-yellow-800
                          @endif">
                        {{ trans()->has('scans.status_'.$scan->status) ? __('scans.status_'.$scan->status) : $scan->status }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($scan->result)
                        <span class="font-bold
                            @if($scan->result->score >= 80) text-green-600
                            @elseif($scan->result->score >= 50) text-yellow-600
                            @else text-red-600
                            @endif">
                            {{ $scan->result->score }}%
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $scan->created_at->diffForHumans() }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('scans.show', $scan) }}" class="text-blue-600 hover:underline mr-3">{{ __('common.view') }}</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $scans->withQueryString()->links() }}
</div>
@else
<div class="bg-white rounded-lg shadow p-12 text-center">
    <p class="text-gray-500 text-lg mb-4">{{ __('scans.no_scans') }}</p>
    <a href="{{ route('scans.create') }}"
        class="inline-block bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
        {{ __('scans.create_first_scan') }}
    </a>
</div>
@endif

<script>
(function() {
    const statusMessages = @json([
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
    ]);

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
                        badge.className = 'px-2 py-1 text-xs rounded scan-status-badge bg-green-100 text-green-800';
                        setTimeout(() => location.reload(), 1500);
                    } else if (data.status === 'failed' && badge) {
                        badge.textContent = '{{ __('scans.status_failed') }}';
                        badge.className = 'px-2 py-1 text-xs rounded scan-status-badge bg-red-100 text-red-800';
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
