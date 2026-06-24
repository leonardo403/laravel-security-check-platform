{{-- resources/views/scans/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Novo Scan de Segurança</h2>

        <form action="{{ route('scans.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">URL do Repositório</label>
                <input type="url" name="repository_url" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="https://github.com/usuario/repositorio">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Branch (opcional)</label>
                <input type="text" name="branch" value="main"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                Iniciar Scan
            </button>
        </form>
    </div>
</div>
@endsection
