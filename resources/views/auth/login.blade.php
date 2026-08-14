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
                <input type="password" name="password" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
            </div>
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
