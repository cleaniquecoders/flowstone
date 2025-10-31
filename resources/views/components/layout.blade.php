<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flowstone</title>

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
<body class="min-h-screen bg-gray-50">
    <div class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-6">
            <a href="{{ route('flowstone.dashboard') }}" class="font-semibold text-gray-900">Flowstone</a>
            <a href="{{ route('flowstone.workflows.index') }}" class="text-gray-600 hover:text-gray-900">Workflows</a>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    @if (class_exists(\Livewire\Livewire::class) && app()->bound('livewire'))
        @livewireScripts
    @endif

    {{-- Flowstone UI bundle (exposes window.FlowstoneUI.mount) --}}
    <script src="{{ (class_exists(\Illuminate\Support\Facades\Vite::class) && $hasViteManifest) ? Vite::asset('vendor/flowstone/flowstone-ui.js') : asset($flowstoneJs) }}" defer></script>
</body>
</html>
