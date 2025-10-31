<x-flowstone::layout>
    <h1 class="text-2xl font-semibold mb-6">Dashboard</h1>

        @if (class_exists(\Livewire\Livewire::class))
            @include('flowstone::partials.livewire.dashboard')
    @else
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
            Livewire is not installed. Please install Livewire to use the Flowstone UI.
        </div>
    @endif
</x-flowstone::layout>
