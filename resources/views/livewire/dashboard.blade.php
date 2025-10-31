<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Workflows</div>
        <div class="text-2xl font-semibold">{{ $workflows ?? 0 }}</div>
    </div>
    <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Enabled</div>
        <div class="text-2xl font-semibold">{{ $enabled ?? 0 }}</div>
    </div>
    <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Places</div>
        <div class="text-2xl font-semibold">{{ $places ?? 0 }}</div>
    </div>
    <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Transitions</div>
        <div class="text-2xl font-semibold">{{ $transitions ?? 0 }}</div>
    </div>
</div>
