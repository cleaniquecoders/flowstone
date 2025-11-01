<x-flowstone::layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden bg-linear-to-br from-white via-flowstone-50/30 to-purple-50/20 rounded-2xl shadow-lg border border-gray-200/50 backdrop-blur-sm">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-flowstone-400/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-purple-400/10 rounded-full blur-3xl"></div>

            <div class="relative p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Left Side: Title -->
                    <div class="flex items-start space-x-5">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-linear-to-br from-flowstone-500 to-purple-600 rounded-2xl blur-lg opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>
                            <div class="relative w-16 h-16 bg-linear-to-br from-flowstone-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform group-hover:scale-105 transition-transform duration-300">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-3xl font-bold bg-linear-to-r from-gray-900 via-flowstone-800 to-purple-900 bg-clip-text text-transparent">
                                Dashboard
                            </h1>
                            <p class="text-gray-600 text-sm leading-relaxed max-w-2xl mt-2">
                                Monitor your workflow performance and activity
                            </p>
                            <div class="flex items-center gap-4 mt-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Real-time monitoring
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Actions -->
                    <div class="flex items-center gap-3 lg:shrink-0">
                        <button type="button"
                            class="group relative inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl hover:bg-white hover:border-flowstone-300 hover:text-flowstone-700 focus:outline-none focus:ring-2 focus:ring-flowstone-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refresh</span>
                        </button>

                        <button type="button"
                            class="group relative inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-flowstone-600 to-purple-600 rounded-xl shadow-lg shadow-flowstone-500/30 hover:shadow-xl hover:shadow-flowstone-500/40 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-flowstone-500 focus:ring-offset-2 transition-all duration-200 ease-out overflow-hidden">
                            <!-- Shimmer effect -->
                            <div class="absolute inset-0 bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>

                            <svg class="w-5 h-5 relative z-10 transition-transform duration-200 group-hover:translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="relative z-10">Export Report</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if (class_exists(\Livewire\Livewire::class))
            @include('flowstone::partials.livewire.dashboard')
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                <div class="flex items-center">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Livewire Required</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Flowstone UI requires Livewire to be installed. Please install Livewire to view dashboard metrics.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-flowstone::layout>
