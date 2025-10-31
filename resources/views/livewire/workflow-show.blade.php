<div class="space-y-6">
    <!-- Workflow Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-linear-to-br from-flowstone-100 to-flowstone-200 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-flowstone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Workflow Visualization</h1>
                    <p class="text-gray-600">Interactive view of your workflow states and transitions</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Export
                </button>

                <button wire:click="refreshGraph" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-flowstone-600 hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh Graph
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Workflow Canvas -->
        <div class="lg:col-span-3">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Workflow Diagram</h2>
                            <p class="text-sm text-gray-600">Visual representation of states and transitions</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center space-x-1 text-xs text-gray-500">
                                <div class="w-3 h-3 bg-flowstone-500 rounded-full"></div>
                                <span>Active</span>
                            </div>
                            <div class="flex items-center space-x-1 text-xs text-gray-500">
                                <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                                <span>Inactive</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="flowstone-canvas"
                     class="h-[560px] bg-gray-50"
                     x-data
                     x-init="window.FlowstoneUI && window.FlowstoneUI.mount($el, @js($graph))"
                     x-on:flowstone:graph:update.window="window.FlowstoneUI && window.FlowstoneUI.mount($el, $event.detail.graph)">
                    <!-- Workflow canvas will be mounted here -->
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Workflow Info -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Info</h3>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">Initial State</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $graph['meta']['initial_marking'] ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">Current State</div>
                        <div class="text-lg font-semibold text-flowstone-600">{{ $graph['meta']['current_marking'] ?? '-' }}</div>
                    </div>

                    <hr class="border-gray-200">

                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-flowstone-600">{{ $graph['meta']['counts']['places'] ?? 0 }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Places</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $graph['meta']['counts']['transitions'] ?? 0 }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Transitions</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                <div class="space-y-3">
                    <a href="{{ route('flowstone.workflows.designer', $workflow) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-flowstone-600 hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Design
                        </a>

                    <button type="button" class="flex items-center w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Export JSON
                    </button>

                    <button type="button" class="flex items-center w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
