<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('common.app_name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen text-white flex items-center justify-center px-6 relative" style="background: url('{{ asset('images/SecurityScan.png') }}') no-repeat center center/cover;    background-position: top;">
    <div class="absolute inset-0 bg-slate-950/70 -z-10"></div>
    <div class="absolute top-6 right-6">
        @include('layouts.language-switcher', ['dark' => true])
    </div>
    <div class="max-w-2xl w-full bg-slate-900/80 border border-slate-700 rounded-2xl shadow-2xl p-10 text-center">
        <h1 class="text-4xl font-bold mb-4">{{ __('common.app_name') }}</h1>
        <p class="text-lg text-slate-300 mb-8">
            {{ __('auth.welcome_tagline') }}
        </p>

        @guest
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-cyan-500 text-slate-950 font-semibold hover:bg-cyan-400">
                    {{ __('auth.login') }}
                </a>
                <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl border border-slate-600 hover:border-cyan-400 hover:text-cyan-300">
                    {{ __('auth.sign_up') }}
                </a>
            </div>
        @else
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl bg-cyan-500 text-slate-950 font-semibold hover:bg-cyan-400">
                    {{ __('auth.go_to_dashboard') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-6 py-3 rounded-xl border border-slate-600 hover:border-red-400 hover:text-red-300">
                        {{ __('common.logout') }}
                    </button>
                </form>
            </div>
        @endguest
    </div>
</body>
</html>
