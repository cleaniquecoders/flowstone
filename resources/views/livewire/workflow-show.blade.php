<div>
    <div class="space-y-6">
        <!-- Workflow Header -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $workflow->is_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $workflow->is_enabled ? '● Active' : '○ Inactive' }}
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm">
                        {{ $workflow->description ?? 'Interactive view of your workflow states and transitions' }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$dispatch('open-edit-modal', { workflowId: {{ $workflow->id }} })"
                        class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-flowstone-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit</span>
                    </button>

                    <a href="{{ route('flowstone.workflows.designer', $workflow) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-flowstone-600 to-purple-600 rounded-lg hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-flowstone-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <span>Designer</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Workflow Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Name</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $workflow->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Type</div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $workflow->type === 'state_machine' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ ucwords(str_replace('_', ' ', $workflow->type)) }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Status</div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $workflow->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $workflow->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Audit Trail</div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $workflow->audit_trail_enabled ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                @if($workflow->audit_trail_enabled)
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Enabled
                                @else
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Disabled
                                @endif
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Created</div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $workflow->created_at->format('M j, Y') }}</div>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="text-sm font-medium text-gray-500 mb-2">Description</div>
                        <div class="text-gray-900">{{ $workflow->description ?? 'No description provided.' }}</div>
                    </div>
                </div>

                <!-- Places (States) -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Places (States)</h3>
                                <p class="text-sm text-gray-600">Available workflow states</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $workflow->places->count() }} total
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if ($workflow->places->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($workflow->places->sortBy('sort_order') as $place)
                                    <div
                                        class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-10 h-10 bg-linear-to-br from-flowstone-100 to-flowstone-200 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-5 h-5 text-flowstone-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $place->name }}</div>
                                            <div class="text-sm text-gray-500">Order: {{ $place->sort_order }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No places defined</h3>
                                <p class="mt-1 text-sm text-gray-500">Add places to define workflow states.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Transitions -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Transitions</h3>
                                <p class="text-sm text-gray-600">State change definitions</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $workflow->transitions->count() }} total
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if ($workflow->transitions->count() > 0)
                            <div class="space-y-4">
                                @foreach ($workflow->transitions->sortBy('sort_order') as $transition)
                                    <div
                                        class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-10 h-10 bg-linear-to-br from-purple-100 to-purple-200 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $transition->name }}</div>
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">{{ $transition->from_place }}</span>
                                                <svg class="inline w-4 h-4 mx-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                </svg>
                                                <span class="font-medium">{{ $transition->to_place }}</span>
                                            </div>
                                            <div class="text-sm text-gray-500">Order: {{ $transition->sort_order }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No transitions defined</h3>
                                <p class="mt-1 text-sm text-gray-500">Add transitions to define state changes.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Meta Information -->
                @if ($workflow->meta && count($workflow->meta) > 0)
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Meta Information</h3>
                                    <p class="text-sm text-gray-600">Custom metadata key-value pairs</p>
                                </div>
                                <button type="button" wire:click="$dispatch('open-metadata-modal')"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-flowstone-600 hover:text-flowstone-700 hover:bg-flowstone-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Manage</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($workflow->meta as $key => $item)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:border-flowstone-300 transition-colors">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $key }}</div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                                                {{ $item['type'] ?? 'string' }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 break-all">
                                            @if (is_array($item['value']))
                                                <code class="text-xs bg-gray-100 px-1 py-0.5 rounded font-mono block overflow-x-auto">{{ json_encode($item['value']) }}</code>
                                            @elseif(is_bool($item['value']))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['value'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $item['value'] ? 'True' : 'False' }}
                                                </span>
                                            @else
                                                {{ $item['value'] }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-500">Places</div>
                            <div class="text-2xl font-bold text-flowstone-600">{{ $workflow->places->count() }}</div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-500">Transitions</div>
                            <div class="text-2xl font-bold text-purple-600">{{ $workflow->transitions->count() }}
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-500">Initial State</div>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $workflow->initial_marking ?? 'Not set' }}</div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-500">Current State</div>
                            <div class="text-sm font-semibold text-flowstone-600">
                                {{ $workflow->marking ?? 'Not set' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Audit Trail -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Audit Trail</h3>
                        @if($workflow->audit_trail_enabled)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Enabled
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Disabled
                            </span>
                        @endif
                    </div>

                    @if($workflow->audit_trail_enabled)
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">All state transitions are being tracked with detailed logs.</p>

                            @php
                                $recentLogs = $workflow->auditLogs()->latest()->take(3)->get();
                            @endphp

                            @if($recentLogs->count() > 0)
                                <div class="space-y-2 mt-3">
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recent Activity</div>
                                    @foreach($recentLogs as $log)
                                        <div class="flex items-start gap-2 p-2 bg-gray-50 rounded-lg text-xs">
                                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-gray-900">{{ $log->transition }}</div>
                                                <div class="text-gray-500">
                                                    {{ $log->from_place }} → {{ $log->to_place }}
                                                </div>
                                                <div class="text-gray-400 mt-1">{{ $log->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <a href="{{ route('flowstone.workflows.show', $workflow) }}#audit-logs"
                                    class="block mt-3 text-sm font-medium text-flowstone-600 hover:text-flowstone-700 text-center">
                                    View All Logs →
                                </a>
                            @else
                                <div class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2 text-xs text-gray-500">No transitions logged yet</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <p class="mt-2 text-xs text-gray-600">Audit trail is disabled</p>
                            <p class="text-xs text-gray-500 mt-1">Enable it to track transitions</p>
                        </div>
                    @endif
                </div>

                <!-- Workflow Metadata -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Workflow Metadata</h3>
                        <button type="button" wire:click="$dispatch('open-metadata-modal')"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-flowstone-600 hover:text-flowstone-700 hover:bg-flowstone-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ $workflow->meta ? 'Manage' : 'Add' }}</span>
                        </button>
                    </div>

                    @if ($workflow->meta && count($workflow->meta) > 0)
                        <div class="space-y-2">
                            @foreach ($workflow->meta as $key => $item)
                                <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 text-sm">{{ $key }}</span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                                                {{ $item['type'] ?? 'string' }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-600 break-all">
                                            @if (is_array($item['value']))
                                                <code
                                                    class="text-xs bg-gray-100 px-1 py-0.5 rounded font-mono">{{ json_encode($item['value']) }}</code>
                                            @elseif(is_bool($item['value']))
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['value'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $item['value'] ? 'True' : 'False' }}
                                                </span>
                                            @else
                                                {{ Str::limit($item['value'], 100) }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No metadata available</h3>
                            <p class="mt-1 text-sm text-gray-500">Click "Add" to create metadata entries.</p>
                        </div>
                    @endif
                </div>

                <!-- Configuration -->
                @if ($workflow->config)
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuration</h3>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-600 font-mono">
                                Type: {{ ucfirst($workflow->type) }}<br>
                                Enabled: {{ $workflow->is_enabled ? 'Yes' : 'No' }}<br>
                                UUID: {{ Str::limit($workflow->uuid, 8) }}...
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Workflow Modal --}}
    @livewire('flowstone.edit-workflow', ['workflow' => $workflow], key('edit-workflow-' . $workflow->id))

    {{-- Manage Metadata Modal --}}
    @livewire('flowstone.manage-workflow-metadata', ['workflow' => $workflow], key('manage-metadata-' . $workflow->id))

</div>
