@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-violet-600 flex items-center justify-center shadow-lg shadow-teal-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs uppercase tracking-[0.25em] text-teal-400 mb-2 font-medium">{{ __('auth.register') }}</p>
            <h2 class="text-2xl font-bold text-white">{{ __('auth.register_title') }}</h2>
        </div>

        <div class="bg-slate-900/80 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/20 p-8 backdrop-blur-sm">
            <form method="POST" action="/register" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-slate-300">{{ __('common.name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                        placeholder="Seu nome">
                    @error('name')<p class="text-rose-400 text-sm mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-slate-300">{{ __('common.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                        placeholder="email@example.com">
                    @error('email')<p class="text-rose-400 text-sm mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-slate-300">{{ __('common.password') }}</label>
                    <input type="password" name="password" required
                        class="w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                        placeholder="••••••••">
                    @error('password')<p class="text-rose-400 text-sm mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5 text-slate-300">{{ __('auth.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500/40 transition-all duration-200"
                        placeholder="••••••••">
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 py-2.5 font-semibold text-white hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30">
                    {{ __('auth.register') }}
                </button>
            </form>
        </div>

        <p class="mt-6 text-sm text-slate-500 text-center">{{ __('auth.have_account') }} <a href="/login" class="text-teal-400 hover:text-teal-300 font-medium transition-colors">{{ __('auth.login') }}</a></p>
    </div>
</div>
@endsection
