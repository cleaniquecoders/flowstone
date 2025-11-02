<div class="workflow-transitions space-y-2">
    @if(count($transitions) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($transitions as $data)
                @if($data['can_apply'])
                    <button
                        type="button"
                        wire:click="applyTransition('{{ $data['name'] }}')"
                        class="{{ $buttonClass }}"
                        title="Apply {{ ucwords(str_replace('_', ' ', $data['name'])) }} transition"
                    >
                        {{ ucwords(str_replace('_', ' ', $data['name'])) }}
                    </button>
                @else
                    <button
                        type="button"
                        disabled
                        class="{{ $disabledClass }}"
                        title="{{ implode(', ', $data['blocker_messages']) }}"
                    >
                        {{ ucwords(str_replace('_', ' ', $data['name'])) }}
                    </button>
                @endif

                @if($showBlockers && !$data['can_apply'] && count($data['blockers']) > 0)
                    <div class="w-full ml-2 text-sm text-red-600">
                        <strong>{{ ucwords(str_replace('_', ' ', $data['name'])) }} blocked:</strong>
                        <ul class="list-disc list-inside">
                            @foreach($data['blocker_messages'] as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-500">
            No transitions available at this time.
        </div>
    @endif
</div>
