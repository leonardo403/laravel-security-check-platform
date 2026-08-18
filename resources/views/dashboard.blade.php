@extends('layouts.app')

@section('content')
@if($stats['subscription_expired'])
<div class="mb-6 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        </div>
        <div>
            <p class="font-medium text-rose-300 text-sm">{{ __('dashboard.subscription_expired') }}</p>
            <p class="text-xs text-rose-400/70">{{ __('dashboard.renew_to_scan') }}</p>
        </div>
    </div>
    <a href="{{ route('plans.index') }}" class="px-4 py-2 rounded-lg bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 transition text-sm font-medium whitespace-nowrap border border-rose-500/20">
        {{ __('dashboard.renew_plan') }}
    </a>
</div>
@endif

<div class="mb-8">
    <div class="flex items-center justify-between">
        @if($stats['plan'])
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-lg shadow-teal-500/20">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ __('common.dashboard') }}</h1>
                <p class="text-sm text-slate-400">{{ trans()->has('plans.name_'.$stats['plan']->slug) ? __('plans.name_'.$stats['plan']->slug) : $stats['plan']->name }}</p>
            </div>
        </div>
        @else
        <div>
            <h1 class="text-2xl font-bold text-white">{{ __('common.dashboard') }}</h1>
        </div>
        <a href="{{ route('plans.index') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold hover:from-amber-400 hover:to-amber-500 transition-all shadow-lg shadow-amber-500/20">
            {{ __('dashboard.subscribe_plan') }}
        </a>
        @endif
    </div>
</div>

@if($stats['plan'])
<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 mb-6 backdrop-blur-sm">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-medium text-slate-200">{{ __('dashboard.monthly_usage') }} — {{ ucfirst(now()->translatedFormat('F')) }}</h3>
        <span class="text-sm text-slate-400">
            {{ __('dashboard.of_scans_used', ['used' => $stats['scans_this_month'], 'max' => $stats['max_scans_per_month']]) }}
        </span>
    </div>
    @php
        $usagePercent = $stats['max_scans_per_month'] > 0
            ? ($stats['scans_this_month'] / $stats['max_scans_per_month']) * 100
            : 0;
    @endphp
    <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden">
        <div class="h-3 rounded-full transition-all duration-700 ease-out
            @if($usagePercent >= 90) bg-gradient-to-r from-rose-500 to-rose-400
            @elseif($usagePercent >= 70) bg-gradient-to-r from-amber-500 to-amber-400
            @else bg-gradient-to-r from-teal-500 to-emerald-400
            @endif"
             style="width: {{ min(100, $usagePercent) }}%"></div>
    </div>
    <div class="flex justify-between mt-2 text-xs text-slate-500">
        <span>{{ __('dashboard.percent_used', ['percent' => number_format($usagePercent, 0)]) }}</span>
        <span>{{ __('dashboard.remaining', ['count' => $stats['scans_remaining']]) }}</span>
    </div>
    @if($stats['scans_remaining'] <= 0)
    <div class="mt-3 text-rose-400 text-sm">
        {{ __('dashboard.limit_reached') }} <a href="{{ route('plans.index') }}" class="underline hover:text-rose-300">{{ __('dashboard.upgrade_plan') }}</a>.
    </div>
    @endif
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm group hover:border-slate-600/50 transition-colors">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('dashboard.total_scans') }}</span>
            <div class="w-8 h-8 rounded-lg bg-sky-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-white">{{ $stats['total_scans'] }}</p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm group hover:border-slate-600/50 transition-colors">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('dashboard.completed') }}</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-emerald-400">{{ $stats['completed_scans'] }}</p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm group hover:border-slate-600/50 transition-colors">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('dashboard.failed') }}</span>
            <div class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-rose-400">{{ $stats['failed_scans'] }}</p>
    </div>
    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-5 backdrop-blur-sm group hover:border-slate-600/50 transition-colors">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-500 uppercase tracking-wider font-medium">{{ __('dashboard.average_score') }}</span>
            <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-violet-400">{{ number_format($stats['average_score'], 1) }}%</p>
    </div>
</div>

<div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm">
    <h2 class="text-lg font-bold text-white mb-4">{{ __('dashboard.recent_scans') }}</h2>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-slate-500 text-xs uppercase tracking-wider">
                    <th class="pb-3 pr-4">{{ __('common.repository') }}</th>
                    <th class="pb-3 pr-4">{{ __('common.status') }}</th>
                    <th class="pb-3 pr-4">{{ __('common.score') }}</th>
                    <th class="pb-3">{{ __('common.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @foreach($stats['recent_scans'] as $scan)
                <tr class="group">
                    <td class="py-3 pr-4 text-sm text-slate-300 group-hover:text-white transition-colors">{{ $scan->repository_url }}</td>
                    <td class="py-3 pr-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs rounded-full font-medium
                            @if($scan->status === 'completed') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                            @elseif($scan->status === 'failed') bg-rose-500/10 text-rose-400 border border-rose-500/20
                            @else bg-amber-500/10 text-amber-400 border border-amber-500/20
                            @endif">
                            {{ trans()->has('scans.status_'.$scan->status) ? __('scans.status_'.$scan->status) : $scan->status }}
                        </span>
                    </td>
                    <td class="py-3 pr-4 text-sm font-semibold
                        @if(($scan->result->score ?? 0) >= 80) text-emerald-400
                        @elseif(($scan->result->score ?? 0) >= 50) text-amber-400
                        @else text-rose-400
                        @endif">
                        {{ $scan->result->score ?? '-' }}
                    </td>
                    <td class="py-3 text-sm text-slate-500">{{ $scan->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
