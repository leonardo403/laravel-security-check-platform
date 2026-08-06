<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Platform MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @auth
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-gray-800">🔒 Security Platform</span>
                    </div>
                    <div class="ml-10 flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                        <a href="{{ route('scans.index') }}" class="text-gray-700 hover:text-gray-900">Scans</a>
                        <a href="{{ route('plans.index') }}" class="text-gray-700 hover:text-gray-900">Planos</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @php
                        $authSubscription = auth()->user()->subscription;
                        $plan = $authSubscription?->isActive() ? $authSubscription->plan : null;
                        $subExpired = $authSubscription !== null && !$authSubscription->isActive();
                    @endphp
                    @if($plan)
                        <span class="px-2 py-1 text-xs rounded-full font-medium
                            @if($plan->slug === 'premium') bg-blue-100 text-blue-800
                            @elseif($plan->slug === 'medium') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $plan->name }}
                        </span>
                    @elseif($subExpired)
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Plano Expirado</span>
                    @endif
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                {{ session('info') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
