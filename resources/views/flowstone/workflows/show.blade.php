<x-flowstone::layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Workflow: {{ $workflow->name }}</h1>
        <a href="{{ route('flowstone.workflows.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back</a>
    </div>

    @if (class_exists(\Livewire\Livewire::class))
        <livewire:flowstone.workflow-show :workflow="$workflow" />
    @else
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
            Livewire is not installed. Please install Livewire to use the Flowstone UI.
        </div>
    @endif
</x-flowstone::layout>
