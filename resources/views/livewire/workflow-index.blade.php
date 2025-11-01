<div class="space-y-6">
    <!-- Page Header -->
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl font-bold bg-linear-to-r from-gray-900 via-flowstone-800 to-purple-900 bg-clip-text text-transparent">
                            Workflows
                        </h1>
                        <p class="text-gray-600 text-sm leading-relaxed max-w-2xl mt-2">
                            Manage and monitor all your workflow configurations
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200/50 p-6">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end">
            <div class="flex-1 w-full lg:w-auto">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Workflows</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search" wire:model.live.debounce.300ms="search"
                        placeholder="Search by name..."
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <div class="flex-1 sm:flex-none">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select wire:model.live="type" id="type"
                        class="block w-full sm:w-48 px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors">
                        <option value="">All types</option>
                        <option value="state_machine">State Machine</option>
                        <option value="workflow">Workflow</option>
                    </select>
                </div>

                <div class="flex-1 sm:flex-none">
                    <label for="enabled" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select wire:model.live="enabled" id="enabled"
                        class="block w-full sm:w-40 px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors">
                        <option value="">All</option>
                        <option value="1">Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>

                @if($search || $type || $enabled)
                    <div class="flex items-end">
                        <button type="button" wire:click="resetFilters"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-flowstone-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Clear</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Workflows Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($workflows as $workflow)
            <div class="relative overflow-hidden bg-white rounded-xl shadow-sm border border-gray-200/50 hover:shadow-xl hover:border-flowstone-300 transition-all duration-300 group backdrop-blur-sm">
                <!-- Decorative gradient -->
                <div class="absolute top-0 right-0 w-20 h-20 bg-linear-to-br from-flowstone-100/50 to-purple-100/50 rounded-bl-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500"></div>

                <div class="relative p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold bg-linear-to-r from-gray-900 to-flowstone-800 bg-clip-text text-transparent group-hover:from-flowstone-600 group-hover:to-purple-600 transition-all duration-300">
                                {{ $workflow->name }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $workflow->description }}</p>
                        </div>
                        <div class="ml-3">
                            @if ($workflow->is_enabled)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Enabled
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Disabled
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-flowstone-600">{{ $workflow->places_count }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Places</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $workflow->transitions_count }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Transitions</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $workflow->type === 'state_machine' ? 'bg-blue-100 text-blue-700 ring-1 ring-blue-600/20' : 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-600/20' }}">
                            {{ ucwords(str_replace('_', ' ', $workflow->type)) }}
                        </span>

                        <div class="flex items-center gap-2">
                            <!-- View Button -->
                            <a href="{{ route('flowstone.workflows.show', $workflow) }}"
                                class="group relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-500 text-white shadow-md shadow-blue-500/25 hover:shadow-lg hover:shadow-blue-500/40 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                                title="View Details">
                                <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            <!-- Designer Button -->
                            <a href="{{ route('flowstone.workflows.designer', $workflow) }}"
                                class="group relative inline-flex items-center justify-center w-9 h-9 rounded-lg bg-linear-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/25 hover:shadow-lg hover:shadow-amber-500/40 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200"
                                title="Open Designer">
                                <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No workflows</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new workflow.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($workflows->hasPages())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $workflows->firstItem() }} to {{ $workflows->lastItem() }} of {{ $workflows->total() }}
                    results
                </div>
                <div class="flex space-x-1">
                    {{ $workflows->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- Create Workflow Modal (Hidden, triggered from header) --}}
    @livewire('flowstone.create-workflow')
</div>
