{{-- resources/views/plans/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="bg-white rounded-lg shadow p-6 {{ $plan->slug === 'premium' ? 'border-2 border-blue-500' : '' }}">
        @if($plan->slug === 'premium')
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

        <form action="{{ route('plans.subscribe', $plan) }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full {{ $plan->slug === 'premium' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white py-2 px-4 rounded-lg transition">
                Assinar {{ $plan->name }}
            </button>
        </form>
    </div>
    @endforeach
</div>
@endsection
