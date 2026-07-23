@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Scans de Segurança</h1>
    <a href="{{ route('scans.create') }}"
        class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
        Novo Scan
    </a>
</div>

@if($scans->count())
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500 bg-gray-50">
                <th class="px-6 py-3">Repositório</th>
                <th class="px-6 py-3">Branch</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Score</th>
                <th class="px-6 py-3">Data</th>
                <th class="px-6 py-3">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scans as $scan)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4">{{ $scan->repository_url }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $scan->branch }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded
                        @if($scan->status === 'completed') bg-green-100 text-green-800
                        @elseif($scan->status === 'failed') bg-red-100 text-red-800
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
                <td class="px-6 py-4">
                    <a href="{{ route('scans.show', $scan) }}" class="text-blue-600 hover:underline">Ver</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $scans->links() }}
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
@endsection
