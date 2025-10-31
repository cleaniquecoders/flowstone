<x-flowstone::layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-linear-to-br from-flowstone-100 to-flowstone-200 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-flowstone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                            <p class="text-gray-600">{{ $workflow->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            @if($workflow->is_enabled)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Disabled
                                </span>
                            @endif

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $workflow->type === 'state_machine' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ ucwords(str_replace('_', ' ', $workflow->type)) }}
                            </span>
                        </div>

                        <div class="flex space-x-3">
                            <a href="{{ route('flowstone.workflows.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Workflows
                            </a>

                            <button id="save-workflow"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-flowstone-600 hover:bg-flowstone-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-flowstone-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span id="save-text">Save Changes</span>
                                <svg id="save-spinner" class="w-4 h-4 ml-2 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Designer -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Workflow Designer</h2>
                            <p class="text-sm text-gray-600">Drag and drop to design your workflow states and transitions</p>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Click nodes to edit, drag to reposition</span>
                        </div>
                    </div>
                </div>

                <div id="workflow-designer" class="w-full h-[800px] bg-gray-50">
                    <!-- Workflow Designer will be mounted here -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const designerElement = document.getElementById('workflow-designer');
        const saveButton = document.getElementById('save-workflow');
        const saveText = document.getElementById('save-text');
        const saveSpinner = document.getElementById('save-spinner');

        // Show loading state initially
        designerElement.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-flowstone-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 font-medium">Loading Workflow Designer...</p>
                    <p class="text-gray-400 text-sm mt-1">Please wait while we initialize the canvas</p>
                </div>
            </div>
        `;

        if (window.FlowstoneUI && window.FlowstoneUI.mountDesigner) {
            // Load existing workflow config
            const workflowConfig = @json($workflow->config && !empty($workflow->config) ? $workflow->config : null);

            window.FlowstoneUI.mountDesigner(designerElement, workflowConfig, function(updatedConfig) {
                console.log('Workflow updated:', updatedConfig);
                // Store the config for saving
                window.currentWorkflowConfig = updatedConfig;
            });
        } else {
            designerElement.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-lg font-medium">Flowstone UI not loaded</p>
                        <p class="text-gray-400 text-sm mt-2 max-w-md">Please check that the JavaScript bundle is properly built and loaded. Try running <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">npm run build</code> or <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">npm run watch</code>.</p>
                    </div>
                </div>
            `;
        }

        // Save functionality
        saveButton.addEventListener('click', function() {
            if (!window.currentWorkflowConfig) {
                // Show error message
                showNotification('No changes to save', 'error');
                return;
            }

            // Show loading state
            saveButton.disabled = true;
            saveText.textContent = 'Saving...';
            saveSpinner.classList.remove('hidden');

            fetch('{{ route("flowstone.workflows.designer", $workflow) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    config: window.currentWorkflowConfig
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Workflow saved successfully!', 'success');
                } else {
                    showNotification('Error saving workflow: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                showNotification('Error saving workflow. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                saveButton.disabled = false;
                saveText.textContent = 'Save Changes';
                saveSpinner.classList.add('hidden');
            });
        });

        // Notification helper
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-50 border-green-200 text-green-800',
                error: 'bg-red-50 border-red-200 text-red-800',
                info: 'bg-blue-50 border-blue-200 text-blue-800'
            };

            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg border ${colors[type]} max-w-sm shadow-lg`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button type="button" class="ml-3 text-current hover:opacity-75" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
    });
    </script>
    @endpush
</x-flowstone::layout>
