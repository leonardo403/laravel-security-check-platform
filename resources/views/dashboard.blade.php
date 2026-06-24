{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Total Scans</h3>
        <p class="text-3xl font-bold">{{ $stats['total_scans'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Completos</h3>
        <p class="text-3xl font-bold text-green-600">{{ $stats['completed_scans'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Falhos</h3>
        <p class="text-3xl font-bold text-red-600">{{ $stats['failed_scans'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm">Score Médio</h3>
        <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['average_score'], 1) }}%</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">Scans Recentes</h2>
    <table class="w-full">
        <thead>
            <tr class="text-left text-gray-500">
                <th class="pb-2">Repositório</th>
                <th class="pb-2">Status</th>
                <th class="pb-2">Score</th>
                <th class="pb-2">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['recent_scans'] as $scan)
            <tr class="border-t">
                <td class="py-2">{{ $scan->repository_url }}</td>
                <td class="py-2">
                    <span class="px-2 py-1 text-xs rounded
                        @if($scan->status === 'completed') bg-green-100 text-green-800
                        @elseif($scan->status === 'failed') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ $scan->status }}
                    </span>
                </td>
                <td class="py-2">{{ $scan->result->score ?? '-' }}</td>
                <td class="py-2">{{ $scan->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
