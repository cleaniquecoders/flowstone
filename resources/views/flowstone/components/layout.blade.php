<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flowstone</title>

    {{-- Livewire --}}
    @if (class_exists(\Livewire\Livewire::class))
           @livewireStyles
    @endif
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

    @if (class_exists(\Livewire\Livewire::class))
           @livewireScripts
    @endif

    {{-- Alpine.js via CDN for convenience --}}
    <script defer src="//unpkg.com/alpinejs" crossorigin="anonymous"></script>
</body>
</html>
