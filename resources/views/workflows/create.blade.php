<x-flowstone::layout>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-flowstone-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-flowstone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Create New Workflow</h1>
                <p class="text-gray-600 mb-8">Get started by defining the basic properties of your workflow.</p>

                @livewire('flowstone::create-workflow')
            </div>
        </div>
    </div>
</x-flowstone::layout>
