<div>
    <!-- Create Workflow Button -->
    <button type="button" wire:click="openModal"
        class="group relative inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-flowstone-600 to-purple-600 rounded-xl shadow-lg shadow-flowstone-500/30 hover:shadow-xl hover:shadow-flowstone-500/40 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-flowstone-500 focus:ring-offset-2 transition-all duration-200 ease-out overflow-hidden">
        <!-- Shimmer effect -->
        <div
            class="absolute inset-0 bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
        </div>

        <svg class="w-5 h-5 relative z-10 transition-transform duration-200 group-hover:rotate-90" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="relative z-10">Create Workflow</span>
    </button>

    <!-- Modal Portal - Rendered at body level -->
    @teleport('body')
        <div x-data="{ show: @entangle('showModal') }" x-show="show" x-on:keydown.escape.window="show = false; $wire.closeModal()"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
            style="display: none;">

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
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl"
                    x-on:click.stop>

                    <form wire:submit="create">
                        <!-- Header -->
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-flowstone-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-flowstone-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Create New Workflow
                                    </h3>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Define the basic properties of your workflow. You can customize places and
                                        transitions in the designer.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="bg-gray-50 px-4 py-5 sm:p-6">
                            <div class="space-y-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Workflow Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" wire:model="name"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('name') border-red-300 @enderror"
                                        placeholder="e.g., Content Publishing Workflow">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Description <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="description" wire:model="description" rows="3"
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
                                        <label for="group" class="block text-sm font-medium text-gray-700 mb-2">
                                            Group
                                        </label>
                                        <input type="text" id="group" wire:model="group"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('group') border-red-300 @enderror"
                                            placeholder="e.g., HR, Operations">
                                        @error('group')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                            Category
                                        </label>
                                        <input type="text" id="category" wire:model="category"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('category') border-red-300 @enderror"
                                            placeholder="e.g., Approval, Publishing">
                                        @error('category')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tags
                                    </label>
                                    <input type="text" id="tags" wire:model="tags"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-flowstone-500 focus:border-flowstone-500 transition-colors @error('tags') border-red-300 @enderror"
                                        placeholder="Comma-separated tags, e.g., urgent, approval, review">
                                    <p class="mt-1 text-xs text-gray-500">Separate multiple tags with commas</p>
                                    @error('tags')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Audit Trail -->
                                <div>
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="audit_trail_enabled" type="checkbox" wire:model="audit_trail_enabled"
                                                class="h-4 w-4 text-flowstone-600 border-gray-300 rounded focus:ring-flowstone-500 transition-colors">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="audit_trail_enabled" class="font-medium text-gray-700">
                                                Enable Audit Trail
                                            </label>
                                            <p class="text-gray-500">
                                                Track all workflow state transitions with detailed logs including user, timestamp, and context.
                                            </p>
                                        </div>
                                    </div>
                                    @error('audit_trail_enabled')
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
                                                <input type="radio" id="type-{{ $typeKey }}" wire:model.live="type"
                                                    value="{{ $typeKey }}" class="sr-only">
                                                <label for="type-{{ $typeKey }}"
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
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-white px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-linear-to-r from-flowstone-500 to-flowstone-600 text-base font-medium text-white hover:from-flowstone-600 hover:to-flowstone-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span wire:loading.remove>Create Workflow</span>
                                <span wire:loading>Creating...</span>
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                <!-- End Modal panel -->
            </div>
            <!-- End Modal Content Container -->
        </div>
    @endteleport

</div>
