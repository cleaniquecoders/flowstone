<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900">Welcome to Flowstone</h1>
        <p class="text-gray-600 text-sm mt-1">Manage and monitor your workflows with ease</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Workflows -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg hover:border-flowstone-200 transition-all duration-300 group overflow-hidden relative">
            <div class="absolute top-0 right-0 w-20 h-20 bg-flowstone-50 rounded-bl-full transform translate-x-6 -translate-y-6 group-hover:bg-flowstone-100 transition-colors"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-linear-to-br from-flowstone-100 to-flowstone-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-flowstone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</div>
                    </div>
                </div>
                <div class="text-sm font-medium text-gray-600 mb-1">Workflows</div>
                <div class="text-3xl font-bold text-gray-900 group-hover:text-flowstone-600 transition-colors">{{ $workflows ?? 0 }}</div>
                <div class="mt-2 flex items-center text-xs text-green-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3"/>
                    </svg>
                    Active systems
                </div>
            </div>
        </div>

        <!-- Enabled Workflows -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg hover:border-green-200 transition-all duration-300 group overflow-hidden relative">
            <div class="absolute top-0 right-0 w-20 h-20 bg-green-50 rounded-bl-full transform translate-x-6 -translate-y-6 group-hover:bg-green-100 transition-colors"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-linear-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</div>
                    </div>
                </div>
                <div class="text-sm font-medium text-gray-600 mb-1">Enabled</div>
                <div class="text-3xl font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ $enabled ?? 0 }}</div>
                <div class="mt-2 flex items-center text-xs text-green-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3"/>
                    </svg>
                    Running smoothly
                </div>
            </div>
        </div>

        <!-- Places -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg hover:border-purple-200 transition-all duration-300 group overflow-hidden relative">
            <div class="absolute top-0 right-0 w-20 h-20 bg-purple-50 rounded-bl-full transform translate-x-6 -translate-y-6 group-hover:bg-purple-100 transition-colors"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-linear-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</div>
                    </div>
                </div>
                <div class="text-sm font-medium text-gray-600 mb-1">Places</div>
                <div class="text-3xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors">{{ $places ?? 0 }}</div>
                <div class="mt-2 flex items-center text-xs text-purple-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3"/>
                    </svg>
                    Workflow states
                </div>
            </div>
        </div>

        <!-- Transitions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg hover:border-amber-200 transition-all duration-300 group overflow-hidden relative">
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-bl-full transform translate-x-6 -translate-y-6 group-hover:bg-amber-100 transition-colors"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-linear-to-br from-amber-100 to-amber-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</div>
                    </div>
                </div>
                <div class="text-sm font-medium text-gray-600 mb-1">Transitions</div>
                <div class="text-3xl font-bold text-gray-900 group-hover:text-amber-600 transition-colors">{{ $transitions ?? 0 }}</div>
                <div class="mt-2 flex items-center text-xs text-amber-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3"/>
                    </svg>
                    State changes
                </div>
            </div>
        </div>
    </div>
</div>
