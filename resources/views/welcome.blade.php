<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\PlatformSetting::platformName() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #020617; }
        .hero-glow {
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(20,184,166,0.14) 0%, rgba(139,92,246,0.07) 50%, transparent 100%);
        }
        .section-glow {
            background: radial-gradient(ellipse 55% 45% at 50% 0%, rgba(20,184,166,0.08) 0%, rgba(139,92,246,0.04) 55%, transparent 100%);
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.8s ease-out both; }
        details summary::-webkit-details-marker { display: none; }
        details[open] .faq-chevron { transform: rotate(180deg); }
        .faq-chevron { transition: transform 0.3s ease; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-200 antialiased overflow-x-hidden">

    <nav class="sticky top-0 z-50 border-b border-slate-700/50 bg-slate-950/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="#top" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-lg shadow-teal-500/20 group-hover:shadow-teal-500/40 transition-shadow">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white tracking-tight">{{ \App\Models\PlatformSetting::platformName() }}</span>
                </a>

                <div class="hidden lg:flex items-center gap-1">
                    <a href="#sobre" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('landing.nav_about') }}</a>
                    <a href="#recursos" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('landing.nav_features') }}</a>
                    <a href="#como-funciona" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('landing.nav_how') }}</a>
                    <a href="#planos" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('landing.nav_plans') }}</a>
                    <a href="#faq" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-200">{{ __('landing.nav_faq') }}</a>
                </div>

                <div class="flex items-center gap-3">
                    @include('layouts.language-switcher')
                    @guest
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 rounded-xl border border-slate-600/50 text-sm font-medium text-slate-300 hover:border-slate-500 hover:text-white hover:bg-slate-800/30 transition-all duration-300">
                            {{ __('auth.login') }}
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white text-sm font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                            {{ __('auth.sign_up') }}
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white text-sm font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                            {{ __('landing.hero_dashboard') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <header id="top" class="relative overflow-hidden">
        <div class="absolute inset-0 hero-glow"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] rounded-full bg-teal-500/5 blur-3xl"></div>
        <div class="absolute top-40 right-0 w-[600px] h-[600px] rounded-full bg-violet-500/5 blur-3xl"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-28 pb-16 sm:pb-24 text-center">
            <div class="animate-fade-up">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium rounded-full border border-teal-500/20 bg-teal-500/10 text-teal-400 backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    {{ __('landing.hero_badge') }}
                </span>

                <h1 class="text-4xl sm:text-6xl font-bold tracking-tight mt-6 mb-5">
                    <span class="text-white">{{ __('landing.hero_title_a') }}</span>
                    <span class="block bg-gradient-to-r from-teal-400 via-teal-300 to-violet-400 bg-clip-text text-transparent">
                        {{ __('landing.hero_title_b') }}
                    </span>
                </h1>

                <p class="text-lg sm:text-xl text-slate-400 leading-relaxed max-w-2xl mx-auto mb-10">
                    {{ __('landing.hero_subtitle') }}
                </p>

                @guest
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                            {{ __('landing.hero_cta_register') }}
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-3.5 rounded-xl border border-slate-600/50 text-slate-300 hover:border-slate-500 hover:text-white hover:bg-slate-800/30 transition-all duration-300">
                            {{ __('auth.login') }}
                        </a>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="inline-flex px-8 py-3.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                        {{ __('landing.hero_dashboard') }}
                    </a>
                @endguest

                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-12 mt-14 text-center">
                    <div>
                        <p class="text-2xl font-bold text-white">4</p>
                        <p class="text-sm text-slate-500 mt-0.5">{{ __('landing.stat_modules') }}</p>
                    </div>
                    <div class="hidden sm:block w-px h-10 bg-slate-800"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">3</p>
                        <p class="text-sm text-slate-500 mt-0.5">{{ __('landing.stat_plans') }}</p>
                    </div>
                    <div class="hidden sm:block w-px h-10 bg-slate-800"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">
                            <svg class="w-6 h-6 mx-auto text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </p>
                        <p class="text-sm text-slate-500 mt-0.5">{{ __('landing.stat_reports') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="sobre" class="relative py-24">
        <div class="absolute inset-0 section-glow"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-teal-400 mb-3">{{ __('landing.about_eyebrow') }}</p>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-6">
                        {{ __('landing.about_title') }}
                    </h2>
                    <div class="text-slate-400 leading-relaxed space-y-4">
                        <p>{{ __('landing.about_p1', ['name' => \App\Models\PlatformSetting::platformName()]) }}</p>
                        <p>{{ __('landing.about_p2') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex gap-4 p-5 rounded-2xl bg-slate-900/60 border border-slate-700/50 backdrop-blur-sm hover:border-slate-600/50 transition-all duration-300">
                        <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.58 15.26A7.5 7.5 0 019.5 9h4a7.5 7.5 0 016.92 4.26M21 16V9a2 2 0 00-2-2H9a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ __('landing.about_point1_title') }}</h3>
                            <p class="text-sm text-slate-400 mt-1">{{ __('landing.about_point1_desc') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-5 rounded-2xl bg-slate-900/60 border border-slate-700/50 backdrop-blur-sm hover:border-slate-600/50 transition-all duration-300">
                        <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ __('landing.about_point2_title') }}</h3>
                            <p class="text-sm text-slate-400 mt-1">{{ __('landing.about_point2_desc') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-5 rounded-2xl bg-slate-900/60 border border-slate-700/50 backdrop-blur-sm hover:border-slate-600/50 transition-all duration-300">
                        <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ __('landing.about_point3_title') }}</h3>
                            <p class="text-sm text-slate-400 mt-1">{{ __('landing.about_point3_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="recursos" class="relative py-24">
        <div class="absolute inset-0 section-glow"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">{{ __('landing.modules_title') }}</h2>
                <p class="text-slate-400 text-lg leading-relaxed">{{ __('landing.modules_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm hover:border-teal-500/30 hover:shadow-xl hover:shadow-teal-500/5 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-2">{{ __('scans.module_security') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ __('scans.module_security_desc') }}</p>
                </div>

                <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm hover:border-teal-500/30 hover:shadow-xl hover:shadow-teal-500/5 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.59 5.09l-2.68-2.68a2 2 0 00-1.41-.58H7a2 2 0 00-2 2v17a2 2 0 002 2h10a2 2 0 002-2V6.5a2 2 0 00-.59-1.41zM9 5h6a2 2 0 002-2V2"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-2">{{ __('scans.module_dependencies') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ __('scans.module_dependencies_desc') }}</p>
                </div>

                <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm hover:border-teal-500/30 hover:shadow-xl hover:shadow-teal-500/5 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-2">{{ __('scans.module_secrets') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ __('scans.module_secrets_desc') }}</p>
                </div>

                <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-6 backdrop-blur-sm hover:border-teal-500/30 hover:shadow-xl hover:shadow-teal-500/5 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-2">{{ __('scans.module_code_quality') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ __('scans.module_code_quality_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="relative py-24">
        <div class="absolute inset-0 section-glow"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">{{ __('landing.how_title') }}</h2>
                <p class="text-slate-400 text-lg leading-relaxed">{{ __('landing.how_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-6 relative">
                <div class="hidden md:block absolute top-7 left-[16.66%] right-[16.66%] h-px bg-gradient-to-r from-teal-500/30 via-violet-500/30 to-teal-500/30"></div>

                <div class="relative text-center">
                    <div class="relative z-10 w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center shadow-lg shadow-teal-500/25 mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </div>
                    <span class="text-xs font-semibold tracking-wider text-teal-400 uppercase">01</span>
                    <h3 class="text-lg font-semibold text-white mt-2 mb-2">{{ __('landing.how_step1_title') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-xs mx-auto">{{ __('landing.how_step1_desc') }}</p>
                </div>

                <div class="relative text-center">
                    <div class="relative z-10 w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2zm0 0v-2m4-6h.01M11 18h.01M17 13h-2m2 4h-2"/></svg>
                    </div>
                    <span class="text-xs font-semibold tracking-wider text-teal-400 uppercase">02</span>
                    <h3 class="text-lg font-semibold text-white mt-2 mb-2">{{ __('landing.how_step2_title') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-xs mx-auto">{{ __('landing.how_step2_desc') }}</p>
                </div>

                <div class="relative text-center">
                    <div class="relative z-10 w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/25 mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-xs font-semibold tracking-wider text-teal-400 uppercase">03</span>
                    <h3 class="text-lg font-semibold text-white mt-2 mb-2">{{ __('landing.how_step3_title') }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-xs mx-auto">{{ __('landing.how_step3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="planos" class="relative py-24">
        <div class="absolute inset-0 section-glow"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">{{ __('landing.plans_title') }}</h2>
                <p class="text-slate-400 text-lg leading-relaxed">{{ __('landing.plans_subtitle') }}</p>
            </div>

            @if($plans->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $plan)
                @php
                    $isMedium = $plan->slug === 'medium';
                    $planName = trans()->has('plans.name_'.$plan->slug) ? __('plans.name_'.$plan->slug) : $plan->name;
                @endphp
                <div class="relative bg-slate-900/60 border rounded-2xl p-7 backdrop-blur-sm transition-all duration-300 hover:shadow-xl
                    {{ $isMedium ? 'border-teal-500/30 shadow-lg shadow-teal-500/5' : 'border-slate-700/50 hover:border-slate-600/50' }}">

                    @if($isMedium)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-teal-500/20 text-teal-400 border border-teal-500/20 font-medium backdrop-blur-sm">{{ __('plans.most_popular') }}</span>
                        </div>
                    @endif

                    <h3 class="text-xl font-bold text-white">{{ $planName }}</h3>
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

                    <a href="{{ route('register') }}" class="block w-full text-center rounded-xl py-2.5 px-4 transition-all duration-300 text-sm font-semibold
                        @if($isMedium)
                            bg-gradient-to-r from-teal-500 to-teal-600 text-white hover:from-teal-400 hover:to-teal-500 shadow-lg shadow-teal-500/20
                        @else
                            bg-slate-800 text-slate-300 border border-slate-700/50 hover:bg-slate-700 hover:text-white
                        @endif">
                        {{ __('landing.plans_cta') }}
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    <section id="faq" class="relative py-24">
        <div class="absolute inset-0 section-glow"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">{{ __('landing.faq_title') }}</h2>
                <p class="text-slate-400 text-lg leading-relaxed">{{ __('landing.faq_subtitle') }}</p>
            </div>

            <div class="space-y-3">
                @foreach(__('landing.faq') as $item)
                <details class="group bg-slate-900/60 border border-slate-700/50 rounded-2xl backdrop-blur-sm transition-all duration-300 hover:border-slate-600/50 [&[open]]:border-teal-500/30">
                    <summary class="flex items-center justify-between gap-4 px-6 py-5 cursor-pointer select-none">
                        <span class="text-sm sm:text-base font-medium text-white">{{ $item['question'] }}</span>
                        <svg class="faq-chevron w-5 h-5 flex-shrink-0 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-6 pb-5">
                        <p class="text-sm text-slate-400 leading-relaxed">{{ $item['answer'] }}</p>
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="relative py-24">
        <div class="absolute inset-0 section-glow"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-teal-500/20 bg-gradient-to-br from-teal-500/10 via-slate-900/40 to-violet-500/10 px-6 sm:px-12 py-14 text-center">
                <div class="absolute inset-0 hero-glow opacity-60"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">{{ __('landing.cta_title') }}</h2>
                    <p class="text-slate-400 text-lg leading-relaxed mb-10 max-w-xl mx-auto">{{ __('landing.cta_subtitle') }}</p>
                    @guest
                        <a href="{{ route('register') }}" class="inline-flex px-10 py-4 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50">
                            {{ __('landing.cta_button') }}
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="inline-flex px-10 py-4 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50">
                            {{ __('landing.hero_dashboard') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                <div class="max-w-sm">
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="font-bold text-white">{{ \App\Models\PlatformSetting::platformName() }}</span>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ __('landing.footer_tagline') }}</p>
                </div>

                <div class="flex flex-wrap gap-x-8 gap-y-3 text-sm">
                    <a href="#sobre" class="text-slate-400 hover:text-white transition-colors">{{ __('landing.nav_about') }}</a>
                    <a href="#recursos" class="text-slate-400 hover:text-white transition-colors">{{ __('landing.nav_features') }}</a>
                    <a href="#como-funciona" class="text-slate-400 hover:text-white transition-colors">{{ __('landing.nav_how') }}</a>
                    <a href="#planos" class="text-slate-400 hover:text-white transition-colors">{{ __('landing.nav_plans') }}</a>
                    <a href="#faq" class="text-slate-400 hover:text-white transition-colors">{{ __('landing.nav_faq') }}</a>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-800/50 text-center text-xs text-slate-600">
                {{ \App\Models\PlatformSetting::platformName() }} &copy; {{ date('Y') }} — {{ __('landing.footer_rights') }}
            </div>
        </div>
    </footer>

</body>
</html>