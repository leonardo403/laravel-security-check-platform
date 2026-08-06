@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-slate-900/80 border border-slate-700 rounded-2xl shadow-2xl p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-cyan-400 mb-3">Recuperar senha</p>
        <h2 class="text-2xl font-bold mb-2">Esqueceu sua senha?</h2>
        <p class="text-sm text-slate-400 mb-6">Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>

        @if(session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1 text-slate-300">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                @error('email')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-xl bg-cyan-500 py-2.5 font-semibold text-slate-950 hover:bg-cyan-400">Enviar link de redefinição</button>
        </form>

        <p class="mt-6 text-sm text-slate-400">Lembrou a senha? <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300">Entrar</a></p>
    </div>
</div>
@endsection
