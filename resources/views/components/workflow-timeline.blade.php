<div class="workflow-timeline">
    @if($logs->count() > 0)
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($logs as $index => $log)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif

                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full {{ $getTimelineColor($log) }} flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>

                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-900">
                                            <span class="font-medium">{{ ucwords(str_replace('_', ' ', $log->transition)) }}</span>
                                            transition from
                                            <span class="font-medium">{{ ucwords(str_replace('_', ' ', $log->from_place)) }}</span>
                                            to
                                            <span class="font-medium">{{ ucwords(str_replace('_', ' ', $log->to_place)) }}</span>
                                        </p>

                                        @if($showUser && $log->user)
                                            <p class="mt-0.5 text-xs text-gray-500">
                                                by {{ $log->user->name ?? 'User #' . $log->user_id }}
                                            </p>
                                        @endif

                                        @if($showContext && $log->context)
                                            <div class="mt-2 text-xs text-gray-600">
                                                <details>
                                                    <summary class="cursor-pointer hover:text-gray-900">View context</summary>
                                                    <pre class="mt-1 p-2 bg-gray-50 rounded text-xs overflow-x-auto">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                                                </details>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                        <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                            {{ $log->created_at->diffForHumans() }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="text-center py-6 text-sm text-gray-500">
            No workflow history available.
        </div>
    @endif
</div>
