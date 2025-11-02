<div class="workflow-status inline-flex items-center gap-2">
    @if($showLabel)
        <span class="text-sm font-medium text-gray-700">Status:</span>
    @endif

    @if(count($places) > 0)
        <div class="flex flex-wrap gap-1">
            @foreach($places as $place => $value)
                <span class="{{ $badgeClass }} {{ $getBadgeColor($place) }}">
                    {{ $formatPlace($place) }}
                </span>
            @endforeach
        </div>
    @else
        <span class="{{ $badgeClass }} bg-gray-100 text-gray-800">
            No Status
        </span>
    @endif
</div>
