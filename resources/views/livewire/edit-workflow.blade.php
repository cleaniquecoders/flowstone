<div>
    <div>
        <!-- Edit Workflow Modal -->
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
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    x-on:click.stop>

                <form wire:submit="update">
                    <!-- Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-flowstone-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-flowstone-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Edit Workflow
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">
                                    Update the basic information for this workflow.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="bg-gray-50 px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Workflow Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="edit-name" wire:model="name"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('name') border-red-300 @enderror"
                                    placeholder="e.g., Content Publishing Workflow">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea id="edit-description" wire:model="description" rows="3"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('description') border-red-300 @enderror"
                                    placeholder="Describe what this workflow is used for..."></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Organization Fields -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Group -->
                                <div>
                                    <label for="edit-group" class="block text-sm font-medium text-gray-700 mb-2">
                                        Group
                                    </label>
                                    <input type="text" id="edit-group" wire:model="group"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('group') border-red-300 @enderror"
                                        placeholder="e.g., HR, Operations">
                                    @error('group')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="edit-category" class="block text-sm font-medium text-gray-700 mb-2">
                                        Category
                                    </label>
                                    <input type="text" id="edit-category" wire:model="category"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('category') border-red-300 @enderror"
                                        placeholder="e.g., Approval, Publishing">
                                    @error('category')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tags -->
                            <div>
                                <label for="edit-tags" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tags
                                </label>
                                <input type="text" id="edit-tags" wire:model="tags"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('tags') border-red-300 @enderror"
                                    placeholder="Comma-separated tags, e.g., urgent, approval, review">
                                <p class="mt-1 text-xs text-gray-500">Separate multiple tags with commas</p>
                                @error('tags')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Workflow Type <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-3">
                                    @foreach ($types as $typeKey => $typeDescription)
                                        <div class="relative">
                                            <input type="radio" id="edit-type-{{ $typeKey }}" wire:model.live="type"
                                                value="{{ $typeKey }}" class="sr-only">
                                            <label for="edit-type-{{ $typeKey }}"
                                                class="block p-4 border rounded-lg cursor-pointer hover:border-flowstone-300 transition-colors {{ $type === $typeKey ? 'border-flowstone-500 bg-flowstone-50' : 'border-gray-300' }}">
                                                <div class="flex items-center">
                                                    <div class="flex-1">
                                                        <div class="font-medium text-gray-900 capitalize">
                                                            {{ str_replace('_', ' ', $typeKey) }}
                                                        </div>
                                                        <div class="text-sm text-gray-600 mt-1">
                                                            {{ $typeDescription }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="w-5 h-5 border-2 rounded-full transition-colors {{ $type === $typeKey ? 'border-flowstone-500 bg-flowstone-500' : 'border-gray-300' }}">
                                                            @if ($type === $typeKey)
                                                                <div class="w-2 h-2 bg-white rounded-full m-0.5"></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Status
                                </label>
                                <div class="flex items-center">
                                    <input type="checkbox" id="edit-is-enabled" wire:model="is_enabled"
                                        class="h-4 w-4 text-flowstone-600 focus:ring-flowstone-500 border-gray-300 rounded">
                                    <label for="edit-is-enabled" class="ml-2 block text-sm text-gray-900">
                                        Enable this workflow
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-white px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-flowstone-600 text-base font-medium text-white hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span wire:loading.remove>Update Workflow</span>
                            <span wire:loading>Updating...</span>
                        </button>
                        <button type="button" wire:click="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </form>
                </div>
                <!-- End Modal panel -->
            </div>
            <!-- End Modal Content Container -->
        </div>
    </div>
</div>
