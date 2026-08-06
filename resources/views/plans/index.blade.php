@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">Planos</h1>
    @if($currentPlan)
    <p class="text-gray-500">Seu plano atual: <span class="font-medium text-blue-600">{{ $currentPlan->name }}</span></p>
    @endif
</div>

@if($expiredPlan)
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
    <p class="font-medium">Sua assinatura do plano {{ $expiredPlan->name }} expirou.</p>
    <p class="text-sm">Renove abaixo para continuar realizando scans.</p>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    @php
        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
        $isExpiredPlan = $expiredPlan && $expiredPlan->id === $plan->id;
        $isMedium = $plan->slug === 'medium';
    @endphp
    <div class="bg-white rounded-lg shadow p-6 {{ $isMedium ? 'border-2 border-blue-500' : '' }} {{ $isCurrent ? 'ring-2 ring-green-500' : '' }}">
        @if($isCurrent)
            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">Seu Plano</span>
        @elseif($isExpiredPlan)
            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-medium">Expirado</span>
        @elseif($isMedium)
            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Mais Popular</span>
        @endif

        <h3 class="text-2xl font-bold mt-2">{{ $plan->name }}</h3>
        <p class="text-4xl font-bold my-4">R$ {{ number_format($plan->price, 2) }}<span class="text-sm text-gray-500">/mês</span></p>

        <ul class="space-y-2 mb-6">
            <li class="flex items-center">
                <span class="mr-2">✓</span>
                {{ $plan->max_scans_per_month }} scans/mês
            </li>
            @foreach($plan->features as $feature => $enabled)
                @if($enabled)
                <li class="flex items-center">
                    <span class="mr-2">✓</span>
                    {{ ucfirst(str_replace('_', ' ', $feature)) }}
                </li>
                @endif
            @endforeach
        </ul>

        @if($isCurrent)
            <div class="w-full bg-green-100 text-green-700 py-2 px-4 rounded-lg text-center font-medium">
                Plano Atual
            </div>
        @else
            <a href="{{ route('plans.checkout', $plan) }}"
                class="block w-full text-center {{ $isExpiredPlan ? 'bg-red-600 hover:bg-red-700' : ($isMedium ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 hover:bg-gray-700') }} text-white py-2 px-4 rounded-lg transition">
                {{ $isExpiredPlan ? 'Renovar ' . $plan->name : 'Assinar ' . $plan->name }}
            </a>
        @endif
    </div>
    @endforeach
</div>
@endsection
