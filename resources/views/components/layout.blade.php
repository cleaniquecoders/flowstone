<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Flowstone - Workflow Management - {{ config('app.name') }}</title>

    {{-- Livewire --}}
    @if (class_exists(\Livewire\Livewire::class) && app()->bound('livewire'))
        @livewireStyles
    @endif

    {{-- Flowstone CSS --}}
    {{ CleaniqueCoders\Flowstone\Flowstone::css() }}

    {{-- Vite HMR for Development --}}
    @if (app()->environment('local') && config('flowstone.vite_dev_server', false))
        @vite(['resources/js/flowstone-ui/init.ts'], 'vendor/flowstone')
    @endif

    {{-- Additional Head Content --}}
    @stack('head')
</head>

<body class="h-full bg-gradient-to-br from-gray-50 to-gray-100 antialiased">
    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('flowstone.dashboard') }}" class="flex items-center space-x-2 group">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span
                            class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-indigo-700 bg-clip-text text-transparent">Flowstone</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('flowstone.dashboard') }}"
                            class="px-4 py-2 text-sm font-medium {{ request()->routeIs('flowstone.dashboard') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-colors">
                            Dashboard
                        </a>
                        <a href="{{ route('flowstone.workflows.index') }}"
                            class="px-4 py-2 text-sm font-medium {{ request()->routeIs('flowstone.workflows.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-colors">
                            Workflows
                        </a>
                    </div>
                </div>

                {{-- User Menu --}}
                @auth
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div
                class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" class="text-green-600 hover:text-green-800"
                    onclick="this.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div
                class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button type="button" class="text-red-600 hover:text-red-800" onclick="this.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Livewire Scripts --}}
    @if (class_exists(\Livewire\Livewire::class) && app()->bound('livewire'))
        @livewireScripts
    @endif

    {{-- Flowstone JS --}}
    {{ CleaniqueCoders\Flowstone\Flowstone::js() }}

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>

</html>
