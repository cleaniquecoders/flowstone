<x-flowstone::layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                        <p class="mt-2 text-gray-600">{{ $workflow->description }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('flowstone.workflows.designer', $workflow) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            View Workflow
                        </a>
                        <button id="save-workflow"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div id="workflow-designer" class="w-full h-[800px]">
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
        
        if (window.FlowstoneUI && window.FlowstoneUI.mountDesigner) {
            // Load existing workflow config
            const workflowConfig = @json($workflow->config && !empty($workflow->config) ? $workflow->config : null);

            window.FlowstoneUI.mountDesigner(designerElement, workflowConfig, function(updatedConfig) {
                console.log('Workflow updated:', updatedConfig);
                // Store the config for saving
                window.currentWorkflowConfig = updatedConfig;
            });
        } else {
            designerElement.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-center"><div class="text-gray-400 mb-2"><svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg></div><p class="text-gray-500 text-sm font-medium">Flowstone UI not loaded</p><p class="text-gray-400 text-xs mt-1">Please check that the JavaScript bundle is properly built and loaded.</p></div></div>';
        }

        // Save functionality
        saveButton.addEventListener('click', function() {
            if (!window.currentWorkflowConfig) {
                alert('No changes to save');
                return;
            }

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
                    alert('Workflow saved successfully!');
                } else {
                    alert('Error saving workflow: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                alert('Error saving workflow');
            });
        });
    });
    </script>
    @endpush
</x-flowstone::layout>
