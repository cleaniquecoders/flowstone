<x-flowstone::layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="relative overflow-hidden bg-linear-to-br from-white via-flowstone-50/30 to-purple-50/20 rounded-2xl shadow-lg border border-gray-200/50 backdrop-blur-sm mb-8">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-flowstone-400/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-purple-400/10 rounded-full blur-3xl"></div>

                <div class="relative p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Left Side: Title and Info -->
                        <div class="flex items-start space-x-5">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-linear-to-br from-flowstone-500 to-purple-600 rounded-2xl blur-lg opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>
                                <div class="relative w-16 h-16 bg-linear-to-br from-flowstone-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform group-hover:scale-105 transition-transform duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <h1 class="text-3xl font-bold bg-linear-to-r from-gray-900 via-flowstone-800 to-purple-900 bg-clip-text text-transparent">
                                        {{ $workflow->name }}
                                    </h1>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $workflow->is_enabled ? 'bg-green-100 text-green-700 ring-1 ring-green-600/20' : 'bg-gray-100 text-gray-700 ring-1 ring-gray-600/20' }}">
                                        {{ $workflow->is_enabled ? '● Active' : '○ Inactive' }}
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed max-w-2xl">
                                    {{ $workflow->description ?? 'Design and configure your workflow states and transitions' }}
                                </p>
                                <div class="flex items-center gap-4 mt-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ ucwords(str_replace('_', ' ', $workflow->type)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Actions -->
                        <div class="flex items-center gap-3 lg:shrink-0">
                            <a href="{{ route('flowstone.workflows.index') }}"
                               class="group relative inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl hover:bg-white hover:border-flowstone-300 hover:text-flowstone-700 focus:outline-none focus:ring-2 focus:ring-flowstone-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>Back to Workflows</span>
                            </a>

                            <button id="save-workflow"
                                    class="group relative inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-flowstone-600 to-purple-600 rounded-xl shadow-lg shadow-flowstone-500/30 hover:shadow-xl hover:shadow-flowstone-500/40 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-flowstone-500 focus:ring-offset-2 transition-all duration-200 ease-out overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                                <!-- Shimmer effect -->
                                <div class="absolute inset-0 bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>

                                <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span id="save-text" class="relative z-10">Save Changes</span>
                                <svg id="save-spinner" class="w-5 h-5 relative z-10 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Workflow Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">What is a Workflow?</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                A workflow models a process where objects can be in <strong>multiple places simultaneously</strong>.
                                Perfect for complex processes like document approval where multiple reviewers work in parallel.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- State Machine Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">What is a State Machine?</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                A state machine holds a <strong>single state at a time</strong>.
                                Ideal for linear processes like order status (pending → processing → completed) or blog post publishing.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Designer -->
            <div id="designer-container" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-300">
                <div class="border-b border-gray-200 px-6 py-5 bg-linear-to-r from-gray-50 to-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-1">Visual Designer</h2>
                            <p class="text-sm text-gray-600">Drag and drop to design your workflow states and transitions</p>
                        </div>
                        <div class="flex items-center gap-4 flex-wrap">
                            <!-- Legend -->
                            <div class="flex items-center gap-4 px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full shadow-sm flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">Place (State)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-orange-500 rounded-lg shadow-sm flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">Transition (Action)</span>
                                </div>
                            </div>

                            <!-- Fullscreen Toggle -->
                            <button id="fullscreen-toggle"
                                    class="group inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-flowstone-300 hover:text-flowstone-700 focus:outline-none focus:ring-2 focus:ring-flowstone-500 transition-all"
                                    title="Toggle fullscreen">
                                <svg id="expand-icon" class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                </svg>
                                <svg id="compress-icon" class="w-4 h-4 hidden transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
                                </svg>
                                <span class="hidden sm:inline">Fullscreen</span>
                            </button>

                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Click to edit • Drag to move</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="workflow-designer" class="w-full h-[calc(100vh-400px)] min-h-[600px] bg-linear-to-br from-gray-50 to-gray-100">
                    <!-- Workflow Designer will be mounted here -->
                </div>
            </div>
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

            window.FlowstoneUI.mountDesigner(designerElement, workflowConfig, designerData, function(updatedConfig, updatedDesigner) {
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

            fetch('{{ route("flowstone.api.workflow.update", $workflow) }}', {
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
                    window.reactFlowInstance.fitView({ padding: 0.3, duration: 800 });
                }
            }, 100);
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
            notification.className = `fixed top-4 right-4 z-[60] p-4 rounded-lg border ${colors[type]} max-w-sm shadow-lg`;
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
