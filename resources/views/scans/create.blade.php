@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    @if(!$plan)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium">{{ __('scans.no_active_plan') }}</p>
                <p class="text-sm">{{ __('scans.subscribe_to_start') }}</p>
            </div>
            <a href="{{ route('plans.index') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm whitespace-nowrap">
                {{ __('scans.view_plans') }}
            </a>
        </div>
    </div>
    @elseif($scansRemaining <= 0)
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium">{{ __('scans.scan_limit_reached') }}</p>
                <p class="text-sm">{{ __('scans.all_scans_used', ['max' => $plan->max_scans_per_month]) }}</p>
            </div>
            <a href="{{ route('plans.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm whitespace-nowrap">
                {{ __('scans.upgrade') }}
            </a>
        </div>
    </div>
    @elseif($scansRemaining <= 3)
    <div class="bg-orange-50 border border-orange-200 text-orange-700 px-4 py-3 rounded mb-4">
        <p class="font-medium">{{ __('scans.few_scans_left', ['count' => $scansRemaining]) }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">{{ __('scans.new_security_scan') }}</h2>

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
                <h3 class="font-medium text-gray-700 mb-3">{{ __('scans.repository_link') }}</h3>
                <input type="url" name="repository_url"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="https://github.com/usuario/repositorio">
                <div class="mt-2">
                    <input type="text" name="branch" value="main" placeholder="{{ __('scans.branch_default') }}"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-700 mb-3">{{ __('scans.upload_env') }}</h3>
                <input type="file" name="env_file" accept=".env,.txt"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">{{ __('scans.env_sensitive_analysis') }}</p>
                <p class="text-xs text-yellow-600 mt-1">
                    {{ __('scans.env_upload_warning') }}
                </p>
            </div>

            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-700 mb-3">{{ __('scans.upload_project') }}</h3>
                <input type="file" name="project_file" accept=".zip"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">{{ __('scans.project_source_code') }}</p>
            </div>

            @if($plan)
            <div class="text-sm text-gray-500 mb-4">
                {{ __('scans.plan_scans_left', ['plan' => $plan->name, 'count' => $scansRemaining]) }}
            </div>
            @endif

            <button type="submit"
                @if(!$plan || $scansRemaining <= 0) disabled
                class="w-full bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed"
                @else
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition"
                @endif>
                {{ __('scans.start_scan') }}
            </button>
        </form>
    </div>
</div>
@endsection
