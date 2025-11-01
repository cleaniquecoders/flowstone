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
    {{-- Minimal Header Navigation --}}
    <nav class="bg-white/80 backdrop-blur-lg border-b border-gray-200/50 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Left: Logo/Brand --}}
                <a href="{{ route('flowstone.dashboard') }}" class="flex items-center space-x-3 group">
                    {{-- Modern Logo with Flow Icon --}}
                    <div class="relative">
                        <div
                            class="w-10 h-10 bg-linear-to-br from-flowstone-500 via-flowstone-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-flowstone-500/25 group-hover:shadow-xl group-hover:shadow-flowstone-500/40 transition-all duration-300 group-hover:scale-105">
                            {{-- Network Flow Icon representing workflow connections --}}
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        {{-- Decorative glow --}}
                        <div
                            class="absolute inset-0 bg-linear-to-br from-flowstone-400 to-purple-500 rounded-xl blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300">
                        </div>
                    </div>

                    {{-- Brand Name --}}
                    <div class="flex flex-col">
                        <span
                            class="text-xl font-bold bg-linear-to-r from-flowstone-600 via-flowstone-700 to-purple-600 bg-clip-text text-transparent leading-tight">
                            Flowstone
                        </span>
                        <span class="text-[10px] font-medium text-gray-500 tracking-wider uppercase">
                            Workflow Engine
                        </span>
                    </div>
                </a>

                {{-- Right: Action Icons --}}
                <div class="flex items-center space-x-3">
                    {{-- Workflows Icon --}}
                    <a href="{{ route('flowstone.workflows.index') }}"
                        class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-flowstone-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="8" height="8" x="3" y="3" rx="2" />
                            <path d="M7 11v4a2 2 0 0 0 2 2h4" />
                            <rect width="8" height="8" x="13" y="13" rx="2" />
                        </svg>
                        <span>Workflows</span>
                    </a>

                    {{-- Add New Workflow Icon --}}
                    <button type="button" onclick="openCreateWorkflowModal()"
                        class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-white bg-linear-to-r from-flowstone-600 to-purple-600 rounded-lg hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-flowstone-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 4v16m8-8H4" />
                        </svg>
                    </button>

                    @auth
                        {{-- User Info (optional, can be hidden on small screens) --}}
                        <div
                            class="hidden sm:flex items-center space-x-2 px-3 py-1.5 bg-linear-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <div
                                class="w-6 h-6 bg-linear-to-br from-flowstone-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span
                                    class="text-xs font-semibold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                        </div>

                        {{-- Logout Button --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="group inline-flex items-center space-x-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg border border-transparent hover:border-red-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div
                class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button"
                    class="p-1 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
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
                <button type="button"
                    class="p-1 text-red-600 hover:text-red-800 hover:bg-red-100 rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                    onclick="this.parentElement.remove()">
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

    {{-- Global Scripts --}}
    <script>
        function openCreateWorkflowModal() {
            // Try to dispatch Livewire event if on workflows page
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('open-create-modal');
            } else {
                // If not on workflows page, navigate there
                window.location.href = '{{ route('flowstone.workflows.index') }}';
            }
        }
    </script>

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>

</html>
