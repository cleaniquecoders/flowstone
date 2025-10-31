import React, { useMemo } from 'react';
import ReactFlow, { Background, Controls, MiniMap, ReactFlowProvider } from 'reactflow';
import 'reactflow/dist/style.css';

export type GraphPayload = {
  nodes: any[];
  edges: any[];
  meta?: Record<string, unknown>;
};

export function ReactFlowCanvas({ graph }: { graph: GraphPayload }) {
  const nodes = useMemo(() => graph?.nodes ?? [], [graph]);
  const edges = useMemo(() => graph?.edges ?? [], [graph]);

  return (
    <div className="w-full h-full rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-gray-50">
      <ReactFlowProvider>
        <ReactFlow
          nodes={nodes}
          edges={edges}
          fitView
          className="bg-white"
          defaultEdgeOptions={{
            style: { strokeWidth: 2 },
            type: 'smoothstep',
          }}
        >
          <Background
            gap={16}
            size={1}
            className="bg-gray-50"
          />
          <Controls
            className="bg-white border border-gray-200 shadow-md rounded-lg"
            showZoom={true}
            showFitView={true}
            showInteractive={true}
          />
          <MiniMap
            className="bg-white border border-gray-200 shadow-md rounded-lg"
            nodeColor="#0ea5e9"
            maskColor="rgba(0, 0, 0, 0.05)"
          />
        </ReactFlow>
      </ReactFlowProvider>
    </div>
  );
}
