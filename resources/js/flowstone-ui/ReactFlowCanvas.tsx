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
    <div style={{ width: '100%', height: '100%' }}>
      <ReactFlowProvider>
        <ReactFlow nodes={nodes} edges={edges} fitView>
          <Background variant="dots" />
          <Controls />
          <MiniMap />
        </ReactFlow>
      </ReactFlowProvider>
    </div>
  );
}
