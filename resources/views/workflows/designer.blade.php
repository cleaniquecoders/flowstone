<x-flowstone::layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
            <!-- Header Section -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $workflow->is_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $workflow->is_enabled ? '● Active' : '○ Inactive' }}
                            </span>
                        </div>
                        <p class="text-gray-600 text-sm">
                            {{ $workflow->description ?? 'Design and configure your workflow states and transitions' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('flowstone.workflows.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-flowstone-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Back</span>
                        </a>

                        <button id="save-workflow"
                            class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-linear-to-r from-flowstone-600 to-purple-600 rounded-lg hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-flowstone-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span id="save-text">Save Changes</span>
                            <svg id="save-spinner" class="w-4 h-4 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Workflow Designer -->
            <div id="designer-container"
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-300">
                <div class="border-b border-gray-200 px-6 py-5 bg-linear-to-r from-gray-50 to-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-1">Workflow Visual Designer</h2>
                            <p class="text-sm text-gray-600">Drag and drop to design your workflow states and
                                transitions</p>
                        </div>
                        <div class="flex items-center gap-4 flex-wrap">
                            <!-- Info Button -->
                            <button id="info-toggle"
                                class="cursor-pointer group inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-flowstone-300 hover:text-flowstone-700 focus:outline-none focus:ring-2 focus:ring-flowstone-500 transition-all"
                                title="View workflow information">
                                <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="hidden sm:inline">Info</span>
                            </button>

                            <!-- Fullscreen Toggle -->
                            <button id="fullscreen-toggle"
                                class="cursor-pointer group inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-flowstone-300 hover:text-flowstone-700 focus:outline-none focus:ring-2 focus:ring-flowstone-500 transition-all"
                                title="Toggle fullscreen">
                                <svg id="expand-icon"
                                    class="w-4 h-4 transition-transform duration-200 group-hover:scale-110"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                </svg>
                                <svg id="compress-icon"
                                    class="w-4 h-4 hidden transition-transform duration-200 group-hover:scale-110"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
                                </svg>
                                <span class="hidden sm:inline">Fullscreen</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="workflow-designer"
                    class="w-full h-[calc(100vh-400px)] min-h-[600px] bg-linear-to-br from-gray-50 to-gray-100">
                    <!-- Workflow Designer will be mounted here -->
                </div>
            </div>

            <!-- Info Modal Container -->
            <div id="info-modal-container"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const designerElement = document.getElementById('workflow-designer');
                const designerContainer = document.getElementById('designer-container');
                const saveButton = document.getElementById('save-workflow');
                const saveText = document.getElementById('save-text');
                const saveSpinner = document.getElementById('save-spinner');
                const fullscreenToggle = document.getElementById('fullscreen-toggle');
                const expandIcon = document.getElementById('expand-icon');
                const compressIcon = document.getElementById('compress-icon');
                const infoToggle = document.getElementById('info-toggle');
                const infoModalContainer = document.getElementById('info-modal-container');

                let isFullscreen = false;

                // Show loading state initially
                designerElement.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="relative w-16 h-16 mx-auto mb-6">
                        <div class="absolute inset-0 border-4 border-flowstone-200 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-flowstone-600 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    <p class="text-gray-700 font-semibold text-lg mb-2">Loading Visual Designer...</p>
                    <p class="text-gray-500 text-sm max-w-md mx-auto">Initializing the interactive canvas where you can design your workflow with places (circles) and transitions (squares)</p>
                </div>
            </div>
        `;

                if (window.FlowstoneUI && window.FlowstoneUI.mountDesigner) {
                    // Load existing workflow config and designer data
                    const workflowConfig = @json($workflow->config && !empty($workflow->config) ? $workflow->config : null);
                    const designerData = @json($workflow->designer ?? null);

                    window.FlowstoneUI.mountDesigner(designerElement, workflowConfig, designerData, function(
                        updatedConfig, updatedDesigner) {
                        console.log('Workflow updated:', updatedConfig);
                        console.log('Designer updated:', updatedDesigner);
                        // Store both config and designer data for saving
                        window.currentWorkflowConfig = updatedConfig;
                        window.currentDesignerData = updatedDesigner;
                    });
                } else {
                    designerElement.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center max-w-lg">
                        <div class="mb-6">
                            <svg class="mx-auto h-20 w-20 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <p class="text-gray-700 text-xl font-semibold mb-3">Designer Not Available</p>
                        <p class="text-gray-600 text-sm mb-4 leading-relaxed">The Flowstone UI JavaScript bundle is not loaded. Please ensure the assets are properly built.</p>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-left">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Build the assets:</p>
                            <div class="space-y-2">
                                <code class="block bg-gray-900 text-green-400 px-3 py-2 rounded text-xs font-mono">npm run build</code>
                                <p class="text-xs text-gray-500 text-center">or for development with hot reload</p>
                                <code class="block bg-gray-900 text-green-400 px-3 py-2 rounded text-xs font-mono">npm run watch</code>
                            </div>
                        </div>
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

                    fetch('{{ route('flowstone.api.workflow.update', $workflow) }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                config: window.currentWorkflowConfig,
                                designer: window.currentDesignerData
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('Workflow saved successfully!', 'success');
                            } else {
                                showNotification('Error saving workflow: ' + (data.message ||
                                    'Unknown error'), 'error');
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

                // Fullscreen toggle functionality
                fullscreenToggle.addEventListener('click', function() {
                    isFullscreen = !isFullscreen;

                    if (isFullscreen) {
                        // Enter fullscreen
                        designerContainer.classList.add('fixed', 'inset-0', 'z-50', 'rounded-none', 'm-0');
                        designerElement.classList.remove('h-[calc(100vh-400px)]', 'min-h-[600px]');
                        designerElement.classList.add('h-screen');
                        expandIcon.classList.add('hidden');
                        compressIcon.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        // Exit fullscreen
                        designerContainer.classList.remove('fixed', 'inset-0', 'z-50', 'rounded-none', 'm-0');
                        designerElement.classList.add('h-[calc(100vh-400px)]', 'min-h-[600px]');
                        designerElement.classList.remove('h-screen');
                        expandIcon.classList.remove('hidden');
                        compressIcon.classList.add('hidden');
                        document.body.style.overflow = '';
                    }

                    // Trigger resize and fit view after a short delay to allow layout to settle
                    setTimeout(() => {
                        window.dispatchEvent(new Event('resize'));

                        // Trigger fit view if React Flow instance is available
                        if (window.reactFlowInstance) {
                            window.reactFlowInstance.fitView({
                                padding: 0.3,
                                duration: 800
                            });
                        }
                    }, 100);
                });

                // Info modal functionality
                infoToggle.addEventListener('click', function() {
                    if (window.FlowstoneUI && window.FlowstoneUI.mountInfoModal) {
                        window.FlowstoneUI.mountInfoModal(infoModalContainer, '{{ $workflow->type }}');
                    } else {
                        showNotification('Info modal not available. Please build assets.', 'error');
                    }
                });

                // Handle ESC key to exit fullscreen
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && isFullscreen) {
                        fullscreenToggle.click();
                    }
                });

                // Notification helper
                function showNotification(message, type = 'info') {
                    const colors = {
                        success: 'bg-green-50 border-green-200 text-green-800',
                        error: 'bg-red-50 border-red-200 text-red-800',
                        info: 'bg-blue-50 border-blue-200 text-blue-800'
                    };

                    const notification = document.createElement('div');
                    notification.className =
                        `fixed top-4 right-4 z-[60] p-4 rounded-lg border ${colors[type]} max-w-sm shadow-lg`;
                    notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button type="button" class="ml-3 p-1 rounded-md text-current hover:bg-black/10 hover:text-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-current focus:ring-offset-1" onclick="this.parentElement.parentElement.remove()">
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
