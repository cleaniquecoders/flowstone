<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flowstone - Workflow Management</title>

    {{-- Livewire --}}
    @if (class_exists(\Livewire\Livewire::class) && app()->bound('livewire'))
        @livewireStyles
    @endif

    {{-- Flowstone UI assets (use Vite when manifest exists, else fallback to configured asset_url) --}}
    @php
        $flowstoneCss = rtrim(config('flowstone.ui.asset_url'), '/') . '/flowstone-ui.css';
        $flowstoneJs = rtrim(config('flowstone.ui.asset_url'), '/') . '/flowstone-ui.js';
        $hasViteManifest = function_exists('public_path') && file_exists(public_path('build/manifest.json'));
    @endphp
    <link rel="stylesheet" href="{{ (class_exists(\Illuminate\Support\Facades\Vite::class) && $hasViteManifest) ? Vite::asset('vendor/flowstone/flowstone-ui.css') : asset($flowstoneCss) }}" />
</head>
<body class="h-full bg-linear-to-br from-gray-50 to-gray-100 antialiased">
    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('flowstone.dashboard') }}" class="flex items-center space-x-2 group">
                        <div class="w-8 h-8 bg-linear-to-br from-flowstone-500 to-flowstone-600 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold bg-linear-to-r from-flowstone-600 to-flowstone-700 bg-clip-text text-transparent">Flowstone</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('flowstone.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-flowstone-600 hover:bg-flowstone-50 rounded-lg transition-colors">
                            Dashboard
                        </a>
                        <a href="{{ route('flowstone.workflows.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-flowstone-600 hover:bg-flowstone-50 rounded-lg transition-colors">
                            Workflows
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    @if (class_exists(\Livewire\Livewire::class) && app()->bound('livewire'))
        @livewireScripts
    @endif

    {{-- Flowstone UI bundle (exposes window.FlowstoneUI.mount) --}}
    <script src="{{ (class_exists(\Illuminate\Support\Facades\Vite::class) && $hasViteManifest) ? Vite::asset('vendor/flowstone/flowstone-ui.js') : asset($flowstoneJs) }}" defer></script>
</body>
</html>
