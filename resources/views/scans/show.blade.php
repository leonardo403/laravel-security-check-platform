@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('scans.index') }}" class="text-blue-600 hover:underline">&larr; Voltar</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Repositório</h3>
        <p class="text-lg font-bold truncate">{{ $scan->repository_url }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Tipo de Scan</h3>
        <p class="text-lg font-bold">
            @if($scan->scan_type === 'repository')
                Link do Repositório
            @elseif($scan->scan_type === 'env')
                Upload .env
            @else
                Upload Projeto
            @endif
        </p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Status</h3>
        <span class="px-2 py-1 text-xs rounded
            @if($scan->status === 'completed') bg-green-100 text-green-800
            @elseif($scan->status === 'failed') bg-red-100 text-red-800
            @else bg-yellow-100 text-yellow-800
            @endif">
            {{ $scan->status }}
        </span>
    </div>
</div>

@if(in_array($scan->status, ['pending', 'processing']))
<div class="bg-white rounded-lg shadow p-6 mb-6" id="progress-container">
    <h3 class="text-gray-700 font-medium mb-4">Progresso do Scan</h3>
    <div class="w-full bg-gray-200 rounded-full h-4">
        <div id="progress-bar" class="bg-blue-600 h-4 rounded-full transition-all duration-500"
             style="width: {{ $scan->progress }}%"></div>
    </div>
    <p class="text-sm text-gray-500 mt-2">
        <span id="progress-text">{{ $scan->progress }}%</span> - 
        <span id="progress-status">
            @if($scan->status === 'pending')
                Aguardando início...
            @else
                Processando...
            @endif
        </span>
    </p>
</div>
@endif

@if($scan->result)
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Score</h3>
        <p class="text-3xl font-bold
            @if($scan->result->score >= 80) text-green-600
            @elseif($scan->result->score >= 50) text-yellow-600
            @else text-red-600
            @endif">
            {{ $scan->result->score }}%
        </p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Duração</h3>
        <p class="text-3xl font-bold">{{ $scan->result->duration_seconds }}s</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Dependências</h3>
        <p class="text-3xl font-bold">{{ $scan->result->dependencies['total'] ?? '-' }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Desatualizadas</h3>
        <p class="text-3xl font-bold text-orange-600">{{ $scan->result->dependencies['outdated'] ?? '-' }}</p>
    </div>
</div>

@if(!empty($scan->result->vulnerabilities))
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Vulnerabilidades ({{ count($scan->result->vulnerabilities) }})</h2>
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500 bg-gray-50">
                <th class="px-4 py-2">Nome</th>
                <th class="px-4 py-2">Severidade</th>
                <th class="px-4 py-2">Pacote</th>
                <th class="px-4 py-2">CVE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scan->result->vulnerabilities as $vuln)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $vuln['name'] ?? '-' }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 text-xs rounded
                        @if(($vuln['severity'] ?? '') === 'critical') bg-red-100 text-red-800
                        @elseif(($vuln['severity'] ?? '') === 'high') bg-orange-100 text-orange-800
                        @elseif(($vuln['severity'] ?? '') === 'medium') bg-yellow-100 text-yellow-800
                        @else bg-blue-100 text-blue-800
                        @endif">
                        {{ $vuln['severity'] ?? '-' }}
                    </span>
                </td>
                <td class="px-4 py-2 text-gray-600">{{ $vuln['package'] ?? '-' }}</td>
                <td class="px-4 py-2 text-gray-600">{{ $vuln['cve'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($scan->result->config_checks))
@php
    $configFails = collect($scan->result->config_checks)->where('status', 'fail');
    $configWarnings = collect($scan->result->config_checks)->where('status', 'warning');
    $configPasses = collect($scan->result->config_checks)->where('status', 'pass');
@endphp
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Verificações de Segurança ({{ count($scan->result->config_checks) }})</h2>
        <div class="flex gap-3 text-sm">
            @if($configFails->count() > 0)
                <span class="text-red-600 font-medium">{{ $configFails->count() }} falha(s)</span>
            @endif
            @if($configWarnings->count() > 0)
                <span class="text-yellow-600 font-medium">{{ $configWarnings->count() }} aviso(s)</span>
            @endif
            @if($configPasses->count() > 0)
                <span class="text-green-600 font-medium">{{ $configPasses->count() }} OK</span>
            @endif
        </div>
    </div>
    <div class="space-y-3">
        @foreach($scan->result->config_checks as $check)
        <div class="flex items-start gap-3 p-3 rounded-lg
            @if($check['status'] === 'fail') bg-red-50 border border-red-200
            @elseif($check['status'] === 'warning') bg-yellow-50 border border-yellow-200
            @else bg-green-50 border border-green-200
            @endif">
            <div class="mt-0.5">
                @if($check['status'] === 'fail')
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                @elseif($check['status'] === 'warning')
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ $check['name'] }}</span>
                    @if($check['severity'] !== 'info')
                        <span class="px-2 py-0.5 text-xs rounded
                            @if($check['severity'] === 'critical') bg-red-200 text-red-800
                            @elseif($check['severity'] === 'high') bg-orange-200 text-orange-800
                            @elseif($check['severity'] === 'medium') bg-yellow-200 text-yellow-800
                            @else bg-blue-200 text-blue-800
                            @endif">
                            {{ $check['severity'] }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ $check['message'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(!empty($scan->result->dependencies['packages']))
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Dependências</h2>
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500 bg-gray-50">
                <th class="px-4 py-2">Pacote</th>
                <th class="px-4 py-2">Versão</th>
                <th class="px-4 py-2">Última</th>
                <th class="px-4 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scan->result->dependencies['packages'] as $pkg)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $pkg['name'] }}</td>
                <td class="px-4 py-2">{{ $pkg['version'] }}</td>
                <td class="px-4 py-2">{{ $pkg['latest'] ?? '-' }}</td>
                <td class="px-4 py-2">
                    @if(empty($pkg['latest']) || $pkg['version'] === $pkg['latest'])
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Atual</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Desatualizada</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-2">Resumo</h2>
    <p class="text-gray-700">{{ $scan->result->summary }}</p>
</div>
@else
<div class="bg-white rounded-lg shadow p-12 text-center" id="waiting-container">
    <p class="text-gray-500 text-lg">Aguardando resultado do scan...</p>
</div>
@endif

@if(in_array($scan->status, ['pending', 'processing']))
<script>
(function() {
    const scanId = {{ $scan->id }};
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressStatus = document.getElementById('progress-status');
    const progressContainer = document.getElementById('progress-container');
    const waitingContainer = document.getElementById('waiting-container');

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

    function pollProgress() {
        fetch(`/scans/${scanId}/progress`)
            .then(response => response.json())
            .then(data => {
                progressBar.style.width = data.progress + '%';
                progressText.textContent = data.progress + '%';
                progressStatus.textContent = statusMessages[data.progress] || 'Processando...';

                if (data.progress < 100 && data.status !== 'failed') {
                    setTimeout(pollProgress, 2000);
                } else {
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => {
                setTimeout(pollProgress, 3000);
            });
    }

    pollProgress();
})();
</script>
@endif
@endsection
