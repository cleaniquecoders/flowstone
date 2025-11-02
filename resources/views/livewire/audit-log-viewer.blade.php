<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            Workflow Audit Trail
        </h2>

        @if($workflowId || $subjectType || $userId || $transition || $place || $startDate || $endDate)
            <button wire:click="clearFilters"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                Clear Filters
            </button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {{-- Workflow Filter --}}
        <div>
            <label for="workflowId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Workflow
            </label>
            <input type="number"
                   wire:model.live="workflowId"
                   id="workflowId"
                   placeholder="Workflow ID"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- User Filter --}}
        <div>
            <label for="userId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                User ID
            </label>
            <input type="number"
                   wire:model.live="userId"
                   id="userId"
                   placeholder="User ID"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- Transition Filter --}}
        <div>
            <label for="transition" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Transition
            </label>
            <input type="text"
                   wire:model.live="transition"
                   id="transition"
                   placeholder="Transition name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- Place Filter --}}
        <div>
            <label for="place" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Place
            </label>
            <input type="text"
                   wire:model.live="place"
                   id="place"
                   placeholder="Place name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- Start Date Filter --}}
        <div>
            <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Start Date
            </label>
            <input type="date"
                   wire:model.live="startDate"
                   id="startDate"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- End Date Filter --}}
        <div>
            <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                End Date
            </label>
            <input type="date"
                   wire:model.live="endDate"
                   id="endDate"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>

        {{-- Per Page --}}
        <div>
            <label for="perPage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Per Page
            </label>
            <select wire:model.live="perPage"
                    id="perPage"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    {{-- Audit Logs Table --}}
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6 cursor-pointer" wire:click="sortBy('created_at')">
                        <div class="flex items-center gap-2">
                            Timestamp
                            @if($sortBy === 'created_at')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white cursor-pointer" wire:click="sortBy('workflow_id')">
                        <div class="flex items-center gap-2">
                            Workflow
                            @if($sortBy === 'workflow_id')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Subject</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white cursor-pointer" wire:click="sortBy('transition')">
                        <div class="flex items-center gap-2">
                            Transition
                            @if($sortBy === 'transition')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">From → To</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white cursor-pointer" wire:click="sortBy('user_id')">
                        <div class="flex items-center gap-2">
                            User
                            @if($sortBy === 'user_id')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900 dark:divide-gray-800">
                @forelse($auditLogs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="text-gray-900 dark:text-white">{{ $log->created_at->format('Y-m-d') }}</div>
                            <div class="text-gray-500 dark:text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            @if($log->workflow)
                                <a href="{{ route('flowstone.workflows.show', $log->workflow) }}"
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    {{ $log->workflow->name }}
                                </a>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="truncate max-w-xs" title="{{ $log->subject_type }}">
                                {{ class_basename($log->subject_type) }}
                            </div>
                            <div class="text-gray-400">#{{ $log->subject_id }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $log->transition }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                @if($log->from_place)
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $log->from_place }}
                                    </span>
                                    <span>→</span>
                                @endif
                                <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ $log->to_place }}
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            @if($log->user)
                                {{ $log->user->name ?? $log->user->email ?? "User #{$log->user_id}" }}
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <button type="button"
                                    title="View details"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $auditLogs->links() }}
    </div>
</div>
