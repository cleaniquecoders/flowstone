<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flowstone</title>

    {{-- Livewire --}}
    @if (class_exists(\Livewire\Livewire::class))
           @livewireStyles
    @endif

    {{-- React Flow CSS via CDN (quick-start). Replace with bundled asset when available. --}}
    <link rel="stylesheet" href="https://esm.sh/reactflow@11/dist/style.css" />
</head>
<body class="min-h-screen bg-gray-50">
    <div class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-6">
            <a href="{{ route('flowstone.dashboard') }}" class="font-semibold text-gray-900">Flowstone</a>
            <a href="{{ route('flowstone.workflows.index') }}" class="text-gray-600 hover:text-gray-900">Workflows</a>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-6">
        {{ $slot }}
    </main>

    @if (class_exists(\Livewire\Livewire::class))
           @livewireScripts
    @endif

    {{-- Alpine.js via CDN for convenience --}}
    <script defer src="//unpkg.com/alpinejs" crossorigin="anonymous"></script>

    {{-- Flowstone React Flow bootstrap (CDN, no build). Provides window.FlowstoneUI.mount(el, graph). --}}
    <script type="module">
        // Guard against double-definition
        if (!window.FlowstoneUI) {
            window.FlowstoneUI = {};
        }

        if (!window.FlowstoneUI.mount) {
            window.FlowstoneUI.mount = async (el, graph) => {
                try {
                    const [React, ReactDOM, ReactFlowPkg] = await Promise.all([
                        import('https://esm.sh/react@18'),
                        import('https://esm.sh/react-dom@18/client'),
                        import('https://esm.sh/reactflow@11?bundle'),
                    ]);

                    const { createElement, useMemo } = React;
                    const { createRoot } = ReactDOM;
                    const { default: ReactFlow, Background, Controls, MiniMap } = ReactFlowPkg;

                    const Component = ({ graph }) => {
                        const nodes = useMemo(() => graph?.nodes ?? [], [graph]);
                        const edges = useMemo(() => graph?.edges ?? [], [graph]);
                        const fitView = true;
                        return createElement(
                            'div',
                            { style: { width: '100%', height: '100%' } },
                            createElement(
                                ReactFlow,
                                { nodes, edges, fitView },
                                createElement(Background, { variant: 'dots' }),
                                createElement(Controls, null),
                                createElement(MiniMap, null),
                            )
                        );
                    };

                    // Reuse existing root if previously mounted on the same element
                    let root = el.__flowstone_root__;
                    if (!root) {
                        root = createRoot(el);
                        el.__flowstone_root__ = root;
                    }
                    root.render(createElement(Component, { graph }));
                } catch (e) {
                    console.error('FlowstoneUI.mount error:', e);
                    if (el) {
                        el.innerHTML = `<div style="padding:12px;color:#b91c1c;">Failed to load React Flow UI. Check console for details.</div>`;
                    }
                }
            };
        }
    </script>
</body>
</html>
