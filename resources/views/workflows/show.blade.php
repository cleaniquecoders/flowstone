<x-flowstone::layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Workflow: {{ $workflow->name }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('flowstone.workflows.designer', $workflow) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Open Designer
            </a>
            <a href="{{ route('flowstone.workflows.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back</a>
        </div>
    </div>

    @if (class_exists(\Livewire\Livewire::class))
        @include('flowstone::partials.livewire.workflows.show')
    @else
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
            Livewire is not installed. Please install Livewire to use the Flowstone UI.
        </div>
    @endif
</x-flowstone::layout>
