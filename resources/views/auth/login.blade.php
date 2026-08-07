@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-slate-900/80 border border-slate-700 rounded-2xl shadow-2xl p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-cyan-400 mb-3">Entrar</p>
        <h2 class="text-2xl font-bold mb-6">Acesse sua conta</h2>

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                @error('email')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">Senha</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="remember" class="mr-2 rounded border-slate-700 bg-slate-950">
                <label class="text-sm text-slate-300">Lembrar-me</label>
            </div>
            <button type="submit" class="w-full rounded-xl bg-cyan-500 py-2.5 font-semibold text-slate-950 hover:bg-cyan-400">Entrar</button>
        </form>

        <p class="mt-4 text-sm text-slate-400"><a href="/forgot-password" class="text-cyan-400 hover:text-cyan-300">Esqueceu sua senha?</a></p>

        <p class="mt-4 text-sm text-slate-400">Ainda não tem conta? <a href="/register" class="text-cyan-400 hover:text-cyan-300">Cadastre-se</a></p>
    </div>
</div>
@endsection
