@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">{{ __('plans.plans') }}</h1>
    @if($currentPlan)
    <p class="text-slate-400 text-sm mt-1">{{ __('plans.your_current_plan', ['plan' => trans()->has('plans.name_'.$currentPlan->slug) ? __('plans.name_'.$currentPlan->slug) : $currentPlan->name]) }}</p>
    @endif
</div>

@if($expiredPlan)
<div class="mb-6 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20">
    <p class="text-sm text-rose-300 font-medium">{{ __('plans.expired_banner', ['plan' => trans()->has('plans.name_'.$expiredPlan->slug) ? __('plans.name_'.$expiredPlan->slug) : $expiredPlan->name]) }}</p>
    <p class="text-xs text-rose-400/70 mt-1">{{ __('plans.renew_below') }}</p>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    @php
        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
        $isExpiredPlan = $expiredPlan && $expiredPlan->id === $plan->id;
        $isMedium = $plan->slug === 'medium';
        $planName = trans()->has('plans.name_'.$plan->slug) ? __('plans.name_'.$plan->slug) : $plan->name;
    @endphp
    <div class="relative bg-slate-900/60 border rounded-2xl p-6 backdrop-blur-sm transition-all duration-300 hover:shadow-xl
        {{ $isMedium ? 'border-teal-500/30 shadow-lg shadow-teal-500/5' : 'border-slate-700/50 hover:border-slate-600/50' }}
        {{ $isCurrent ? 'ring-1 ring-emerald-500/30' : '' }}">

        @if($isCurrent)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 font-medium backdrop-blur-sm">{{ __('plans.your_plan') }}</span>
            </div>
        @elseif($isExpiredPlan)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/20 font-medium backdrop-blur-sm">{{ __('plans.expired') }}</span>
            </div>
        @elseif($isMedium)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-teal-500/20 text-teal-400 border border-teal-500/20 font-medium backdrop-blur-sm">{{ __('plans.most_popular') }}</span>
            </div>
        @endif

        <h3 class="text-xl font-bold text-white mt-2">{{ $planName }}</h3>
        <div class="mt-4 mb-6">
            <span class="text-4xl font-bold text-white">${{ number_format($plan->price, 2) }}</span>
            <span class="text-sm text-slate-500">{{ __('plans.per_month') }}</span>
        </div>

        <ul class="space-y-3 mb-8">
            <li class="flex items-center gap-2.5 text-sm text-slate-300">
                <div class="w-5 h-5 rounded-full bg-teal-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                {{ __('plans.scans_per_month', ['count' => $plan->max_scans_per_month]) }}
            </li>
            @foreach($plan->features as $feature => $enabled)
                @if($enabled)
                <li class="flex items-center gap-2.5 text-sm text-slate-300">
                    <div class="w-5 h-5 rounded-full bg-teal-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    {{ trans()->has('plans.features_'.$feature) ? __('plans.features_'.$feature) : ucfirst(str_replace('_', ' ', $feature)) }}
                </li>
                @endif
            @endforeach
        </ul>

        @if($isCurrent)
            <div class="w-full rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 py-2.5 px-4 text-center text-sm font-medium">
                {{ __('plans.current_plan') }}
            </div>
        @else
            <a href="{{ route('plans.checkout', $plan) }}"
                class="block w-full text-center rounded-xl py-2.5 px-4 transition-all duration-300 text-sm font-semibold
                @if($isExpiredPlan)
                    bg-gradient-to-r from-rose-500 to-rose-600 text-white hover:from-rose-400 hover:to-rose-500 shadow-lg shadow-rose-500/20
                @elseif($isMedium)
                    bg-gradient-to-r from-teal-500 to-teal-600 text-white hover:from-teal-400 hover:to-teal-500 shadow-lg shadow-teal-500/20
                @else
                    bg-slate-800 text-slate-300 border border-slate-700/50 hover:bg-slate-700 hover:text-white
                @endif">
                {{ $isExpiredPlan ? __('plans.renew_plan', ['plan' => $planName]) : __('plans.subscribe_plan', ['plan' => $planName]) }}
            </a>
        @endif
    </div>
    @endforeach
</div>
@endsection
