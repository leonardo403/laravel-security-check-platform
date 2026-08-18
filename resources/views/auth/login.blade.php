@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-slate-900/80 border border-slate-700 rounded-2xl shadow-2xl p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-cyan-400 mb-3">{{ __('auth.login') }}</p>
        <h2 class="text-2xl font-bold mb-6">{{ __('auth.login_title') }}</h2>

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('common.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                @error('email')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('common.password') }}</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 pr-10 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-200">
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
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
            <div class="flex items-center">
                <input type="checkbox" name="remember" class="mr-2 rounded border-slate-700 bg-slate-950">
                <label class="text-sm text-slate-300">{{ __('auth.remember_me') }}</label>
            </div>
            <button type="submit" class="w-full rounded-xl bg-cyan-500 py-2.5 font-semibold text-slate-950 hover:bg-cyan-400">{{ __('auth.login') }}</button>
        </form>

        <p class="mt-4 text-sm text-slate-400"><a href="/forgot-password" class="text-cyan-400 hover:text-cyan-300">{{ __('auth.forgot_password') }}</a></p>

        <p class="mt-4 text-sm text-slate-400">{{ __('auth.no_account') }} <a href="/register" class="text-cyan-400 hover:text-cyan-300">{{ __('auth.sign_up') }}</a></p>
    </div>
</div>
@endsection
