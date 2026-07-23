<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Platform MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-6">
    <div class="max-w-2xl w-full bg-slate-900/80 border border-slate-700 rounded-2xl shadow-2xl p-10 text-center">
        <p class="text-sm uppercase tracking-[0.3em] text-cyan-400 mb-4">MVP</p>
        <h1 class="text-4xl font-bold mb-4">Security Platform</h1>
        <p class="text-lg text-slate-300 mb-8">
            Uma experiência simples para realizar login, criar conta e acessar o dashboard do projeto.
        </p>

        @guest
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-cyan-500 text-slate-950 font-semibold hover:bg-cyan-400">
                    Entrar
                </a>
                <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl border border-slate-600 hover:border-cyan-400 hover:text-cyan-300">
                    Cadastrar
                </a>
            </div>
        @else
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl bg-cyan-500 text-slate-950 font-semibold hover:bg-cyan-400">
                    Ir para o Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-6 py-3 rounded-xl border border-slate-600 hover:border-red-400 hover:text-red-300">
                        Sair
                    </button>
                </form>
            </div>
        @endguest
    </div>
</body>
</html>
