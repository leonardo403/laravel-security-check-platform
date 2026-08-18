@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-lg shadow-teal-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs uppercase tracking-[0.25em] text-teal-400 mb-2 font-medium">{{ __('auth.login') }}</p>
            <h2 class="text-2xl font-bold text-white">{{ __('auth.login_title') }}</h2>
        </div>

        <div class="bg-slate-900/80 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/20 p-8 backdrop-blur-sm">
            <form method="POST" action="/login" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-slate-300">{{ __('common.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                        placeholder="email@example.com">
                    @error('email')<p class="text-rose-400 text-sm mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-slate-300">{{ __('common.password') }}</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required
                            class="w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-2.5 pr-11 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 transition-colors">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-teal-500 focus:ring-teal-500/40 focus:ring-offset-0">
                        <span class="text-sm text-slate-400">{{ __('auth.remember_me') }}</span>
                    </label>
                    <a href="/forgot-password" class="text-sm text-teal-400 hover:text-teal-300 transition-colors">{{ __('auth.forgot_password') }}</a>
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 py-2.5 font-semibold text-white hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30">
                    {{ __('auth.login') }}
                </button>
            </form>
        </div>

        <p class="mt-6 text-sm text-slate-500 text-center">{{ __('auth.no_account') }} <a href="/register" class="text-teal-400 hover:text-teal-300 font-medium transition-colors">{{ __('auth.sign_up') }}</a></p>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }
</script>
@endsection
