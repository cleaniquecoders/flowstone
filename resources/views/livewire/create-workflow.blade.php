<div>
    <!-- Create Workflow Button -->
    <button type="button" wire:click="openModal"
            class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-flowstone-600 hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Workflow
    </button>

    <!-- Modal -->
    <div x-data="{ show: @entangle('showModal') }"
         x-show="show"
         x-on:keydown.escape.window="show = false; $wire.closeModal()"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <!-- Backdrop -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-on:click="show = false; $wire.closeModal()"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity cursor-pointer"></div>

            <!-- Modal panel -->
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <form wire:submit="create">
                    <!-- Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-flowstone-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-flowstone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Create New Workflow
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">
                                    Define the basic properties of your workflow. You can customize places and transitions in the designer.
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

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Workflow Type <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-3">
                                    @foreach($types as $typeKey => $typeDescription)
                                        <div class="relative">
                                            <input type="radio" id="type-{{ $typeKey }}" wire:model="type" value="{{ $typeKey }}"
                                                   class="sr-only peer">
                                            <label for="type-{{ $typeKey }}"
                                                   class="block p-4 border border-gray-300 rounded-lg cursor-pointer hover:border-flowstone-300 peer-checked:border-flowstone-500 peer-checked:bg-flowstone-50 transition-colors">
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
                                                        <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-flowstone-500 peer-checked:bg-flowstone-500 transition-colors">
                                                            <div class="w-2 h-2 bg-white rounded-full m-0.5 peer-checked:bg-white"></div>
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
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-flowstone-600 text-base font-medium text-white hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove>Create Workflow</span>
                            <span wire:loading>Creating...</span>
                        </button>
                        <button type="button" wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
