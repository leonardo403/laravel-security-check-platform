@extends('layouts.app')

@section('content')
@if(!$plan)
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-medium">Você não possui um plano ativo.</p>
            <p class="text-sm">Assine um plano para continuar scaneando seus projetos.</p>
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

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Scans de Segurança</h1>
    <a href="{{ route('scans.create') }}"
        @if(!$plan || $scansRemaining <= 0)
        class="bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed"
        @else
        class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition"
        @endif>
        Novo Scan
    </a>
</div>

@if($scans->count())
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500 bg-gray-50">
                <th class="px-6 py-3">Repositório</th>
                <th class="px-6 py-3">Tipo</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Score</th>
                <th class="px-6 py-3">Data</th>
                <th class="px-6 py-3 text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scans as $scan)
            <tr class="border-t hover:bg-gray-50 {{ in_array($scan->status, ['pending', 'processing']) ? 'scan-active' : '' }}">
                <td class="px-6 py-4">
                    <div class="truncate max-w-xs">{{ $scan->repository_url }}</div>
                    @if(in_array($scan->status, ['pending', 'processing']))
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500 scan-progress-bar"
                             style="width: {{ $scan->progress }}%"
                             data-scan-id="{{ $scan->id }}"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 scan-progress-text" data-scan-id="{{ $scan->id }}">
                        {{ $scan->progress }}%
                        @if($scan->status === 'pending')
                            - Aguardando fila...
                        @elseif($scan->progress < 30)
                            - Preparando...
                        @elseif($scan->progress < 50)
                            - Analisando vulnerabilidades...
                        @elseif($scan->progress < 65)
                            - Analisando dependências...
                        @elseif($scan->progress < 85)
                            - Verificando configurações...
                        @elseif($scan->progress < 95)
                            - Calculando score...
                        @else
                            - Gerando relatório...
                        @endif
                    </div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($scan->scan_type === 'repository')
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Repositório</span>
                    @elseif($scan->scan_type === 'env')
                        <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">.env</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-800">Upload</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded scan-status-badge"
                          data-scan-id="{{ $scan->id }}"
                          @if($scan->status === 'completed') bg-green-100 text-green-800
                          @elseif($scan->status === 'failed') bg-red-100 text-red-800
                          @elseif($scan->status === 'processing') bg-blue-100 text-blue-800
                          @else bg-yellow-100 text-yellow-800
                          @endif">
                        {{ $scan->status }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($scan->result)
                        <span class="font-bold
                            @if($scan->result->score >= 80) text-green-600
                            @elseif($scan->result->score >= 50) text-yellow-600
                            @else text-red-600
                            @endif">
                            {{ $scan->result->score }}%
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $scan->created_at->diffForHumans() }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('scans.show', $scan) }}" class="text-blue-600 hover:underline mr-3">Ver</a>
                    @if(!in_array($scan->status, ['pending', 'processing']))
                    <!--form action="{{ route('scans.destroy', $scan) }}" method="POST" class="inline"
                          onsubmit="return confirm('Tem certeza que deseja excluir este scan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                    </form-->
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $scans->withQueryString()->links() }}
</div>
@else
<div class="bg-white rounded-lg shadow p-12 text-center">
    <p class="text-gray-500 text-lg mb-4">Nenhum scan encontrado.</p>
    <a href="{{ route('scans.create') }}"
        class="inline-block bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
        Criar primeiro scan
    </a>
</div>
@endif

<script>
(function() {
    const statusMessages = {
        5: 'Preparando scan...',
        10: 'Carregando fonte...',
        15: 'Fonte carregada...',
        20: 'Análise iniciada...',
        30: 'Analisando dependências...',
        45: 'Verificando vulnerabilidades...',
        65: 'Analisando configurações...',
        85: 'Calculando score...',
        95: 'Gerando relatório...',
        100: 'Concluído!'
    };

    function getProgressLabel(progress) {
        if (progress < 30) return 'Preparando...';
        if (progress < 45) return 'Analisando dependências...';
        if (progress < 65) return 'Verificando vulnerabilidades...';
        if (progress < 85) return 'Analisando configurações...';
        if (progress < 95) return 'Calculando score...';
        return 'Gerando relatório...';
    }

    const activeScanIds = [...document.querySelectorAll('.scan-progress-bar')]
        .map(el => el.dataset.scanId);

    if (activeScanIds.length === 0) return;

    function pollActiveScans() {
        const stillActive = [];

        activeScanIds.forEach(scanId => {
            fetch(`/scans/${scanId}/progress`)
                .then(r => r.json())
                .then(data => {
                    const bar = document.querySelector(`.scan-progress-bar[data-scan-id="${scanId}"]`);
                    const text = document.querySelector(`.scan-progress-text[data-scan-id="${scanId}"]`);
                    const badge = document.querySelector(`.scan-status-badge[data-scan-id="${scanId}"]`);

                    if (bar) bar.style.width = data.progress + '%';
                    if (text) text.textContent = data.progress + '% - ' + getProgressLabel(data.progress);

                    if (data.status === 'completed' && badge) {
                        badge.textContent = 'completed';
                        badge.className = 'px-2 py-1 text-xs rounded scan-status-badge bg-green-100 text-green-800';
                        setTimeout(() => location.reload(), 1500);
                    } else if (data.status === 'failed' && badge) {
                        badge.textContent = 'failed';
                        badge.className = 'px-2 py-1 text-xs rounded scan-status-badge bg-red-100 text-red-800';
                        setTimeout(() => location.reload(), 1500);
                    } else if (data.progress < 100) {
                        stillActive.push(scanId);
                    }
                })
                .catch(() => {});
        });

        if (stillActive.length > 0) {
            setTimeout(pollActiveScans, 2000);
        }
    }

    pollActiveScans();
})();
</script>
@endsection
