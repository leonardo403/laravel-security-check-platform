<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\PlatformSetting::platformName() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #020617; }
        .hero-glow {
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(20,184,166,0.12) 0%, rgba(139,92,246,0.06) 50%, transparent 100%);
        }
    </style>
</head>
<body class="min-h-screen text-white flex items-center justify-center px-6 relative overflow-hidden">
    <div class="absolute inset-0 hero-glow"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] rounded-full bg-teal-500/5 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] rounded-full bg-violet-500/5 blur-3xl"></div>

    <div class="absolute top-6 right-6 z-10">
        @include('layouts.language-switcher', ['dark' => true])
    </div>

    <div class="relative z-10 max-w-xl w-full text-center">
        <div class="mb-8 inline-flex items-center justify-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-2xl shadow-teal-500/25">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>

        <h1 class="text-4xl sm:text-5xl font-bold mb-4 tracking-tight">
            <span class="bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">{{ \App\Models\PlatformSetting::platformName() }}</span>
        </h1>
        <p class="text-lg text-slate-400 mb-10 leading-relaxed max-w-md mx-auto">
            {{ __('auth.welcome_tagline') }}
        </p>

        @guest
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('login') }}" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                    {{ __('auth.login') }}
                </a>
                <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-xl border border-slate-600/50 text-slate-300 hover:border-slate-500 hover:text-white hover:bg-slate-800/30 transition-all duration-300">
                    {{ __('auth.sign_up') }}
                </a>
            </div>
        @else
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                    {{ __('auth.go_to_dashboard') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-8 py-3.5 rounded-xl border border-slate-600/50 text-slate-300 hover:border-rose-500/50 hover:text-rose-400 hover:bg-rose-500/5 transition-all duration-300">
                        {{ __('common.logout') }}
                    </button>
                </form>
            </div>
        @endguest
    </div>
</body>
</html>
