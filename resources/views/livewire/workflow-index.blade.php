<div class="space-y-6">
    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Workflows</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="search" wire:model.debounce.300ms="search"
                           placeholder="Search by name..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors">
                </div>
            </div>

            <div class="flex gap-3">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select wire:model="type" id="type"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors">
                        <option value="">All types</option>
                        <option value="state_machine">State Machine</option>
                        <option value="workflow">Workflow</option>
                    </select>
                </div>

                <div>
                    <label for="enabled" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select wire:model="enabled" id="enabled"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors">
                        <option value="">All</option>
                        <option value="1">Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflows Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($workflows as $workflow)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg hover:border-flowstone-200 transition-all duration-200 group overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-flowstone-600 transition-colors">
                                {{ $workflow->name }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $workflow->description }}</p>
                        </div>
                        <div class="ml-3">
                            @if($workflow->is_enabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
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

                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $workflow->type === 'state_machine' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                            {{ ucwords(str_replace('_', ' ', $workflow->type)) }}
                        </span>

                        <a href="{{ route('flowstone.workflows.show', $workflow) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-flowstone-600 hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </a>
                        <a href="{{ route('flowstone.workflows.designer', $workflow) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-flowstone-600 hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Design
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No workflows</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new workflow.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($workflows->hasPages())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $workflows->firstItem() }} to {{ $workflows->lastItem() }} of {{ $workflows->total() }} results
                </div>
                <div class="flex space-x-1">
                    {{ $workflows->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
