<div class="space-y-4">
    <div class="flex items-center gap-3">
        <input type="text" wire:model.debounce.300ms="search" placeholder="Search by name..." class="border rounded px-3 py-2 w-full md:w-1/3">
        <select wire:model="type" class="border rounded px-3 py-2">
            <option value="">All types</option>
            <option value="state_machine">state_machine</option>
            <option value="workflow">workflow</option>
        </select>
        <select wire:model="enabled" class="border rounded px-3 py-2">
            <option value="">All</option>
            <option value="1">Enabled</option>
            <option value="0">Disabled</option>
        </select>
    </div>

    <div class="bg-white border rounded overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Places</th>
                    <th class="px-4 py-2">Transitions</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($workflows as $wf)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $wf->name }}</td>
                    <td class="px-4 py-2">{{ $wf->type }}</td>
                    <td class="px-4 py-2">{{ $wf->places_count }}</td>
                    <td class="px-4 py-2">{{ $wf->transitions_count }}</td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('flowstone.workflows.show', $wf) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="p-3">
            {{ $workflows->links() }}
        </div>
    </div>
</div>
