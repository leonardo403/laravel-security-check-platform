@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    @if(!$plan)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium">Você não possui um plano ativo.</p>
                <p class="text-sm">Assine um plano para começar a scanear seus projetos.</p>
            </div>
            <a href="{{ route('plans.index') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm whitespace-nowrap">
                Ver Planos
            </a>
        </div>
    </div>
    @elseif($scansRemaining <= 0)
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium">Limite de scans atingido!</p>
                <p class="text-sm">Você utilizou todos os {{ $plan->max_scans_per_month }} scans do seu plano este mês.</p>
            </div>
            <a href="{{ route('plans.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm whitespace-nowrap">
                Fazer Upgrade
            </a>
        </div>
    </div>
    @elseif($scansRemaining <= 3)
    <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded mb-4">
        <p class="font-medium">Atenção: apenas {{ $scansRemaining }} scan(s) restante(s) este mês.</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Novo Scan de Segurança</h2>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('scans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-700 mb-3">Link do Repositório</h3>
                <input type="url" name="repository_url"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="https://github.com/usuario/repositorio">
                <div class="mt-2">
                    <input type="text" name="branch" value="main" placeholder="Branch (padrão: main)"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-700 mb-3">Upload .env</h3>
                <input type="file" name="env_file" accept=".env,.txt"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">Análise de variáveis sensíveis</p>
            </div>

            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-700 mb-3">Upload Projeto (ZIP)</h3>
                <input type="file" name="project_file" accept=".zip"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">Código fonte do projeto</p>
            </div>

            @if($plan)
            <div class="text-sm text-gray-500 mb-4">
                Plano <span class="font-medium">{{ $plan->name }}</span> — {{ $scansRemaining }} scan(s) restante(s) este mês
            </div>
            @endif

            <button type="submit"
                @if(!$plan || $scansRemaining <= 0) disabled
                class="w-full bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed"
                @else
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition"
                @endif>
                Iniciar Scan
            </button>
        </form>
    </div>
</div>
@endsection
