<div>
    <!-- Manage Transition Metadata Modal -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-on:keydown.escape.window="show = false; $wire.closeModal()"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">

        <!-- Backdrop with Blur Glass Effect -->
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            x-on:click="show = false; $wire.closeModal()"
            class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm transition-all cursor-pointer"></div>

        <!-- Modal Content Container -->
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                x-on:click.stop>

                <!-- Header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Manage Transition Metadata: {{ $transition->name }}
                            </h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Add, edit, or remove metadata for this workflow transition.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Close Button (outside modal box) -->
                <button type="button" wire:click="closeModal"
                    class="absolute -top-3 -right-3 z-50 bg-white text-gray-400 hover:text-gray-900 rounded-full focus:ring-2 focus:ring-orange-300 p-2 hover:bg-gray-100 shadow-lg cursor-pointer transition-all duration-200 hover:scale-110">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>

                <!-- Body -->
                <div class="bg-gray-50 px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column: Add/Edit Form -->
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">
                                {{ $isEditing ? 'Edit Metadata' : 'Add Metadata' }}
                            </h4>

                            <form wire:submit="addMetadata">
                                <div class="space-y-4">
                                    <!-- Key -->
                                    <div>
                                        <label for="transition-meta-key" class="block text-sm font-medium text-gray-700 mb-2">
                                            Key <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="transition-meta-key" wire:model="key"
                                            {{ $isEditing && $editingKey ? 'disabled' : '' }}
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed @error('key') border-red-300 @enderror"
                                            placeholder="e.g., roles, guard, description">
                                        @error('key')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Type -->
                                    <div>
                                        <label for="transition-meta-type" class="block text-sm font-medium text-gray-700 mb-2">
                                            Type <span class="text-red-500">*</span>
                                        </label>
                                        <select id="transition-meta-type" wire:model.live="type"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('type') border-red-300 @enderror">
                                            @foreach ($types as $typeKey => $typeLabel)
                                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Value -->
                                    <div>
                                        <label for="transition-meta-value" class="block text-sm font-medium text-gray-700 mb-2">
                                            Value <span class="text-red-500">*</span>
                                        </label>

                                        @if ($type === 'text' || $type === 'array' || $type === 'roles' || $type === 'guard')
                                            <textarea id="transition-meta-value" wire:model="value" rows="4"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors font-mono text-sm @error('value') border-red-300 @enderror"
                                                placeholder="{{ $type === 'array' || $type === 'roles' ? '["role1", "role2"]' : ($type === 'guard' ? 'is_granted("ROLE_USER")' : 'Enter text...') }}"></textarea>
                                        @elseif($type === 'boolean')
                                            <select id="transition-meta-value" wire:model="value"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('value') border-red-300 @enderror">
                                                <option value="1">True</option>
                                                <option value="0">False</option>
                                            </select>
                                        @elseif($type === 'date')
                                            <input type="date" id="transition-meta-value" wire:model="value"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('value') border-red-300 @enderror">
                                        @elseif($type === 'datetime')
                                            <input type="datetime-local" id="transition-meta-value" wire:model="value"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('value') border-red-300 @enderror">
                                        @elseif($type === 'integer')
                                            <input type="number" step="1" id="transition-meta-value" wire:model="value"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('value') border-red-300 @enderror"
                                                placeholder="e.g., 42">
                                        @elseif($type === 'numeric')
                                            <input type="number" step="0.01" id="transition-meta-value" wire:model="value"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('value') border-red-300 @enderror"
                                                placeholder="e.g., 3.14">
                                        @else
                                            <input type="text" id="transition-meta-value" wire:model="value"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('value') border-red-300 @enderror"
                                                placeholder="Enter value...">
                                        @endif

                                        @error('value')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror

                                        @if ($type === 'array' || $type === 'roles')
                                            <p class="mt-1 text-xs text-gray-500">Enter valid JSON array format</p>
                                        @elseif($type === 'guard')
                                            <p class="mt-1 text-xs text-gray-500">Symfony expression language syntax</p>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-3 pt-2">
                                        <button type="submit"
                                            class="flex-1 inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:text-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            <span wire:loading.remove>{{ $isEditing ? 'Update' : 'Add' }}</span>
                                            <span wire:loading>{{ $isEditing ? 'Updating...' : 'Adding...' }}</span>
                                        </button>

                                        @if ($isEditing)
                                            <button type="button" wire:click="cancelEdit"
                                                class="inline-flex justify-center items-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:text-sm transition-all duration-200">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Right Column: Existing Metadata -->
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">
                                Current Metadata ({{ count($metadata) }})
                            </h4>

                            @if (count($metadata) > 0)
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    @foreach ($metadata as $metaKey => $metaItem)
                                        @php
                                            // Handle both old format (flat values) and new format (value/type structure)
                                            $isStructured = is_array($metaItem) && isset($metaItem['type']);
                                            $itemValue = $isStructured ? $metaItem['value'] : $metaItem;
                                            $itemType = $isStructured ? $metaItem['type'] : (is_bool($metaItem) ? 'boolean' : (is_array($metaItem) ? 'array' : 'string'));
                                        @endphp
                                        <div
                                            class="flex items-start justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-orange-300 transition-colors">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-gray-900 text-sm break-all">
                                                        {{ $metaKey }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">
                                                        {{ $itemType }}
                                                    </span>
                                                </div>
                                                <div class="mt-1 text-sm text-gray-600 break-all">
                                                    @if (is_array($itemValue))
                                                        <code
                                                            class="text-xs bg-gray-100 px-1 py-0.5 rounded">{{ json_encode($itemValue) }}</code>
                                                    @elseif(is_bool($itemValue))
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $itemValue ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $itemValue ? 'True' : 'False' }}
                                                        </span>
                                                    @else
                                                        {{ Str::limit($itemValue, 50) }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 ml-3">
                                                <button type="button" wire:click="editMetadata('{{ $metaKey }}')"
                                                    class="p-1.5 text-orange-600 hover:bg-orange-100 rounded transition-colors"
                                                    title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    wire:click="deleteMetadata('{{ $metaKey }}')"
                                                    wire:confirm="Are you sure you want to delete this metadata?"
                                                    class="p-1.5 text-red-600 hover:bg-red-100 rounded transition-colors"
                                                    title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No metadata</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by adding a metadata entry.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-white px-4 py-3 sm:px-6 border-t border-gray-200 flex justify-end">
                    <button type="button" wire:click="closeModal"
                        class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:text-sm transition-all duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
