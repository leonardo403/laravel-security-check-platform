@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    @if(!$plan)
    <div class="mb-6 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <p class="font-medium text-amber-300 text-sm">{{ __('scans.no_active_plan') }}</p>
                <p class="text-xs text-amber-400/70">{{ __('scans.subscribe_to_start') }}</p>
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

    <div class="mb-6">
        <a href="{{ route('scans.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('scans.back') }}
        </a>
    </div>

    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm">
        <h2 class="text-xl font-bold text-white mb-6">{{ __('scans.new_security_scan') }}</h2>

        @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('scans.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-700/30">
                <h3 class="font-medium text-slate-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    {{ __('scans.repository_link') }}
                </h3>
                <input type="url" name="repository_url"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-700/50 bg-slate-800/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                    placeholder="https://github.com/usuario/repositorio">
                <div class="mt-3">
                    <input type="text" name="branch" value="main" placeholder="{{ __('scans.branch_default') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-700/50 bg-slate-800/50 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200">
                </div>
            </div>

            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-700/30">
                <h3 class="font-medium text-slate-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('scans.upload_env') }}
                </h3>
                <input type="file" name="env_file" accept=".env,.txt"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-700/50 bg-slate-800/50 text-white text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-500/20 file:text-teal-400 hover:file:bg-teal-500/30 file:cursor-pointer focus:outline-none focus:ring-2 focus:ring-teal-500/40 transition-all duration-200">
                <p class="text-xs text-slate-500 mt-2">{{ __('scans.env_sensitive_analysis') }}</p>
                <p class="text-xs text-amber-400/70 mt-1">{{ __('scans.env_upload_warning') }}</p>
            </div>

            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-700/30">
                <h3 class="font-medium text-slate-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    {{ __('scans.upload_project') }}
                </h3>
                <input type="file" name="project_file" accept=".zip"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-700/50 bg-slate-800/50 text-white text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-500/20 file:text-teal-400 hover:file:bg-teal-500/30 file:cursor-pointer focus:outline-none focus:ring-2 focus:ring-teal-500/40 transition-all duration-200">
                <p class="text-xs text-slate-500 mt-2">{{ __('scans.project_source_code') }}</p>
            </div>

            @if($plan)
            <div class="text-sm text-slate-500">
                {{ __('scans.plan_scans_left', ['plan' => $plan->name, 'count' => $scansRemaining]) }}
            </div>
            @endif

            <button type="submit"
                @if(!$plan || $scansRemaining <= 0) disabled
                class="w-full rounded-xl bg-slate-800 text-slate-500 py-3 cursor-not-allowed border border-slate-700/50 font-medium"
                @else
                class="w-full rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 py-3 font-semibold text-white hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30"
                @endif>
                {{ __('scans.start_scan') }}
            </button>
        </form>
    </div>
</div>
@endsection
