@extends('layouts.app')

@section('content')
@if($stats['subscription_expired'])
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-medium">{{ __('dashboard.subscription_expired') }}</p>
            <p class="text-sm">{{ __('dashboard.renew_to_scan') }}</p>
        </div>
        <a href="{{ route('plans.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm whitespace-nowrap">
            {{ __('dashboard.renew_plan') }}
        </a>
    </div>
</div>
@endif

<div class="mb-6">
    <div class="flex items-center justify-between">
        @if($stats['plan'])
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg px-6 py-3 shadow">
            <div class="text-sm opacity-80">{{ __('dashboard.current_plan') }}</div>
            <div class="text-xl font-bold">{{ trans()->has('plans.name_'.$stats['plan']->slug) ? __('plans.name_'.$stats['plan']->slug) : $stats['plan']->name }}</div>
        </div>
        @else
        <a href="{{ route('plans.index') }}" class="bg-yellow-500 text-white rounded-lg px-6 py-3 shadow hover:bg-yellow-600 transition">
            {{ __('dashboard.subscribe_plan') }}
        </a>
        @endif
    </div>
</div>

@if($stats['plan'])
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-medium text-gray-700">{{ __('dashboard.monthly_usage') }} - {{ ucfirst(now()->translatedFormat('F')) }}</h3>
        <span class="text-sm text-gray-500">
            {{ __('dashboard.of_scans_used', ['used' => $stats['scans_this_month'], 'max' => $stats['max_scans_per_month']]) }}
        </span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-4">
        @php
            $usagePercent = $stats['max_scans_per_month'] > 0
                ? ($stats['scans_this_month'] / $stats['max_scans_per_month']) * 100
                : 0;
        @endphp
        <div class="h-4 rounded-full transition-all duration-500
            @if($usagePercent >= 90) bg-red-500
            @elseif($usagePercent >= 70) bg-yellow-500
            @else bg-green-500
            @endif"
             style="width: {{ min(100, $usagePercent) }}%"></div>
    </div>
    <div class="flex justify-between mt-2 text-sm text-gray-500">
        <span>{{ __('dashboard.percent_used', ['percent' => number_format($usagePercent, 0)]) }}</span>
        <span>{{ __('dashboard.remaining', ['count' => $stats['scans_remaining']]) }}</span>
    </div>
    @if($stats['scans_remaining'] <= 0)
    <div class="mt-3 text-red-600 text-sm font-medium">
        {{ __('dashboard.limit_reached') }} <a href="{{ route('plans.index') }}" class="underline">{{ __('dashboard.upgrade_plan') }}</a>.
    </div>
    @endif
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">{{ __('dashboard.total_scans') }}</h3>
        <p class="text-3xl font-bold">{{ $stats['total_scans'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">{{ __('dashboard.completed') }}</h3>
        <p class="text-3xl font-bold text-green-600">{{ $stats['completed_scans'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">{{ __('dashboard.failed') }}</h3>
        <p class="text-3xl font-bold text-red-600">{{ $stats['failed_scans'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">{{ __('dashboard.average_score') }}</h3>
        <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['average_score'], 1) }}%</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">{{ __('dashboard.recent_scans') }}</h2>
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500">
                <th class="pb-2">{{ __('common.repository') }}</th>
                <th class="pb-2">{{ __('common.status') }}</th>
                <th class="pb-2">{{ __('common.score') }}</th>
                <th class="pb-2">{{ __('common.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['recent_scans'] as $scan)
            <tr class="border-t">
                <td class="py-2">{{ $scan->repository_url }}</td>
                <td class="py-2">
                    <span class="px-2 py-1 text-xs rounded
                        @if($scan->status === 'completed') bg-green-100 text-green-800
                        @elseif($scan->status === 'failed') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ trans()->has('scans.status_'.$scan->status) ? __('scans.status_'.$scan->status) : $scan->status }}
                    </span>
                </td>
                <td class="py-2">{{ $scan->result->score ?? '-' }}</td>
                <td class="py-2">{{ $scan->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
