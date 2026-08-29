@extends('layouts.app')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">{{ __('admin.settings_title') }}</h1>
    <p class="text-slate-400 text-sm mt-1">{{ __('admin.settings_subtitle') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-900/60 border border-teal-500/30 text-teal-300 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ __('admin.settings_title') }}
        </a>
        <a href="{{ route('admin.plans') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700/50 text-slate-300 text-sm font-medium hover:border-slate-600/50 hover:text-white transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            {{ __('admin.plans') }}
        </a>
    </div>

    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm">
            @csrf

            <h2 class="text-lg font-semibold text-white mb-6">{{ __('admin.general_settings') }}</h2>

            <div class="space-y-5">
                <div>
                    <label for="platform_name" class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('admin.platform_name') }}</label>
                    <input type="text" id="platform_name" name="platform_name" value="{{ old('platform_name', $platformName) }}"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/50 px-4 py-2.5 text-sm text-white focus:border-teal-500/50 focus:ring-1 focus:ring-teal-500/30 outline-none transition-all"
                        required>
                    @error('platform_name')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="support_email" class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('admin.support_email') }}</label>
                    <input type="email" id="support_email" name="support_email" value="{{ old('support_email', $supportEmail) }}"
                        class="w-full rounded-xl bg-slate-950 border border-slate-700/50 px-4 py-2.5 text-sm text-white focus:border-teal-500/50 focus:ring-1 focus:ring-teal-500/30 outline-none transition-all">
                    @error('support_email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" {{ $maintenanceMode ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded accent-teal-500">
                    <span>
                        <span class="block text-sm font-medium text-slate-300">{{ __('admin.maintenance_mode') }}</span>
                        <span class="block text-xs text-slate-500 mt-0.5">{{ __('admin.maintenance_mode_hint') }}</span>
                    </span>
                </label>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white hover:from-teal-400 hover:to-teal-500 shadow-lg shadow-teal-500/20 px-6 py-2.5 text-sm font-semibold transition-all duration-300">
                    {{ __('admin.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection