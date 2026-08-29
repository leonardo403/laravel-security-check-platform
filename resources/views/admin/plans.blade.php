@extends('layouts.app')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">{{ __('admin.plans_title') }}</h1>
    <p class="text-slate-400 text-sm mt-1">{{ __('admin.plans_subtitle') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700/50 text-slate-300 text-sm font-medium hover:border-slate-600/50 hover:text-white transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ __('admin.settings_title') }}
        </a>
        <a href="{{ route('admin.plans') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-900/60 border border-teal-500/30 text-teal-300 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            {{ __('admin.plans') }}
        </a>
    </div>

    <div class="lg:col-span-2 space-y-6">
        @foreach($plans as $plan)
        @php
            $planName = trans()->has('plans.name_'.$plan->slug) ? __('plans.name_'.$plan->slug) : $plan->name;
            $features = \App\Models\SubscriptionPlan::FEATURES;
        @endphp
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm">
            @csrf

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-white">{{ $planName }}</h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">{{ __('admin.subscriptions_count', ['count' => $plan->subscriptions_count]) }}</span>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }} class="rounded accent-teal-500">
                        <span class="text-xs text-slate-400">{{ __('admin.is_active') }}</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('admin.plan_name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/50 px-3.5 py-2 text-sm text-white focus:border-teal-500/50 focus:ring-1 focus:ring-teal-500/30 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('admin.price') }}</label>
                    <input type="number" name="price" value="{{ old('price', $plan->price) }}" step="0.01" min="0"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/50 px-3.5 py-2 text-sm text-white focus:border-teal-500/50 focus:ring-1 focus:ring-teal-500/30 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">{{ __('admin.max_scans') }}</label>
                    <input type="number" name="max_scans_per_month" value="{{ old('max_scans_per_month', $plan->max_scans_per_month) }}" min="1"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/50 px-3.5 py-2 text-sm text-white focus:border-teal-500/50 focus:ring-1 focus:ring-teal-500/30 outline-none transition-all">
                </div>
            </div>

            <div class="mb-6">
                <p class="block text-xs font-medium text-slate-400 mb-3">{{ __('admin.features') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($features as $feature)
                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-slate-950/60 border border-slate-800 cursor-pointer hover:border-slate-700 transition-colors">
                        <input type="checkbox" name="feature_{{ $feature }}" value="1" {{ ($plan->features[$feature] ?? false) ? 'checked' : '' }} class="rounded accent-teal-500">
                        <span class="text-xs text-slate-300">{{ trans()->has('plans.features_'.$feature) ? __('plans.features_'.$feature) : ucfirst(str_replace('_', ' ', $feature)) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            @error('name')<p class="mb-2 text-xs text-rose-400">{{ $message }}</p>@enderror
            @error('price')<p class="mb-2 text-xs text-rose-400">{{ $message }}</p>@enderror
            @error('max_scans_per_month')<p class="mb-2 text-xs text-rose-400">{{ $message }}</p>@enderror

            <div class="flex justify-end">
                <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white hover:from-teal-400 hover:to-teal-500 shadow-lg shadow-teal-500/20 px-5 py-2.5 text-sm font-semibold transition-all duration-300">
                    {{ __('admin.save_changes') }}
                </button>
            </div>
        </form>
        @endforeach
    </div>
</div>

@endsection