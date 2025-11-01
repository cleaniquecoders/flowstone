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
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-flowstone-500">
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
                @if ($workflow->meta)
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Meta Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($workflow->meta, JSON_PRETTY_PRINT) }}</pre>
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

                <!-- Workflow Metadata -->
                @if ($workflow->meta)
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Metadata</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($workflow->meta, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Metadata</h3>
                        <div class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No metadata available</p>
                        </div>
                    </div>
                @endif

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

</div>
