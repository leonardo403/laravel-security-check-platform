@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">{{ __('plans.plans') }}</h1>
    @if($currentPlan)
    <p class="text-gray-500">{{ __('plans.your_current_plan', ['plan' => trans()->has('plans.name_'.$currentPlan->slug) ? __('plans.name_'.$currentPlan->slug) : $currentPlan->name]) }}</p>
    @endif
</div>

@if($expiredPlan)
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
    <p class="font-medium">{{ __('plans.expired_banner', ['plan' => trans()->has('plans.name_'.$expiredPlan->slug) ? __('plans.name_'.$expiredPlan->slug) : $expiredPlan->name]) }}</p>
    <p class="text-sm">{{ __('plans.renew_below') }}</p>
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
    <div class="bg-white rounded-lg shadow p-6 {{ $isMedium ? 'border-2 border-blue-500' : '' }} {{ $isCurrent ? 'ring-2 ring-green-500' : '' }}">
        @if($isCurrent)
            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">{{ __('plans.your_plan') }}</span>
        @elseif($isExpiredPlan)
            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium">{{ __('plans.expired') }}</span>
        @elseif($isMedium)
            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">{{ __('plans.most_popular') }}</span>
        @endif

        <h3 class="text-2xl font-bold mt-2">{{ $planName }}</h3>
        <p class="text-4xl font-bold my-4">R$ {{ number_format($plan->price, 2) }}<span class="text-sm text-gray-500">{{ __('plans.per_month') }}</span></p>

        <ul class="space-y-2 mb-6">
            <li class="flex items-center">
                <span class="mr-2">✓</span>
                {{ __('plans.scans_per_month', ['count' => $plan->max_scans_per_month]) }}
            </li>
            @foreach($plan->features as $feature => $enabled)
                @if($enabled)
                <li class="flex items-center">
                    <span class="mr-2">✓</span>
                    {{ trans()->has('plans.features_'.$feature) ? __('plans.features_'.$feature) : ucfirst(str_replace('_', ' ', $feature)) }}
                </li>
                @endif
            @endforeach
        </ul>

        @if($isCurrent)
            <div class="w-full bg-green-100 text-green-700 py-2 px-4 rounded-lg text-center font-medium">
                {{ __('plans.current_plan') }}
            </div>
        @else
            <a href="{{ route('plans.checkout', $plan) }}"
                class="block w-full text-center {{ $isExpiredPlan ? 'bg-red-600 hover:bg-red-700' : ($isMedium ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 hover:bg-gray-700') }} text-white py-2 px-4 rounded-lg transition">
                {{ $isExpiredPlan ? __('plans.renew_plan', ['plan' => $planName]) : __('plans.subscribe_plan', ['plan' => $planName]) }}
            </a>
        @endif
    </div>
    @endforeach
</div>
@endsection
