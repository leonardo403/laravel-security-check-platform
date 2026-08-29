<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\PlatformSetting::platformName() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #020617; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-200 antialiased">
    @auth
    <nav class="sticky top-0 z-50 border-b border-slate-700/50 bg-slate-900/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/dashboard" class="flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-lg shadow-teal-500/20 group-hover:shadow-teal-500/40 transition-shadow">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight">{{ \App\Models\PlatformSetting::platformName() }}</span>
                    </a>
                    <div class="hidden sm:flex sm:items-center sm:ml-8 sm:space-x-1">
                        <a href="/dashboard" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('common.dashboard') }}</a>
                        <a href="/scans" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('common.scans') }}</a>
                        <a href="/plans" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('common.plans') }}</a>
                        @if(auth()->user()->isAdmin())
                            <a href="/admin/settings" class="px-3 py-2 rounded-lg text-sm font-medium text-amber-300 hover:text-white hover:bg-amber-500/10 transition-all duration-200">{{ __('admin.nav') }}</a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @include('layouts.language-switcher')
                    @php
                        $authSubscription = auth()->user()->subscription;
                        $plan = $authSubscription?->isActive() ? $authSubscription->plan : null;
                        $subExpired = $authSubscription !== null && !$authSubscription->isActive();
                    @endphp
                    @if($plan)
                        <span class="hidden sm:inline-flex items-center px-2.5 py-1 text-xs rounded-full font-medium backdrop-blur-sm
                            @if($plan->slug === 'premium') bg-violet-500/15 text-violet-400 border border-violet-500/20
                            @elseif($plan->slug === 'medium') bg-teal-500/15 text-teal-400 border border-teal-500/20
                            @else bg-slate-500/15 text-slate-400 border border-slate-500/20
                            @endif">
                            {{ trans()->has('plans.name_'.$plan->slug) ? __('plans.name_'.$plan->slug) : $plan->name }}
                        </span>
                    @elseif($subExpired)
                        <span class="hidden sm:inline-flex items-center px-2.5 py-1 text-xs rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/20 font-medium">{{ __('common.expired_plan') }}</span>
                    @endif
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-700/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:inline text-sm text-slate-300">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="text-xs text-slate-500 hover:text-rose-400 transition-colors px-2 py-1 rounded-lg hover:bg-slate-800/60">{{ __('common.logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @else
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-end">
        @include('layouts.language-switcher')
    </div>
    @endauth

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                {{ session('info') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('warning') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-slate-800/50 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-600">
            {{ \App\Models\PlatformSetting::platformName() }} &copy; {{ date('Y') }}
        </div>
    </footer>
</body>
</html>
