<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="bg-white border rounded p-2">
            <div
                id="flowstone-canvas"
                class="h-[560px] bg-gray-50 rounded border"
                x-data
                x-init="window.FlowstoneUI && window.FlowstoneUI.mount($el, @js($graph))"
                x-on:flowstone:graph:update.window="window.FlowstoneUI && window.FlowstoneUI.mount($el, $event.detail.graph)"
            ></div>
        </div>
    </div>
    <div class="lg:col-span-1">
        <div class="bg-white border rounded p-4 space-y-2">
            <div class="text-sm text-gray-500">Initial</div>
            <div class="font-medium">{{ $graph['meta']['initial_marking'] ?? '-' }}</div>

            <div class="text-sm text-gray-500 mt-3">Current</div>
            <div class="font-medium">{{ $graph['meta']['current_marking'] ?? '-' }}</div>

            <div class="text-sm text-gray-500 mt-3">Counts</div>
            <div class="font-medium">Places: {{ $graph['meta']['counts']['places'] ?? 0 }}</div>
            <div class="font-medium">Transitions: {{ $graph['meta']['counts']['transitions'] ?? 0 }}</div>

            <button type="button" class="mt-4 inline-flex items-center px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                wire:click="refreshGraph">
                Refresh graph
            </button>
        </div>
    </div>
</div>
