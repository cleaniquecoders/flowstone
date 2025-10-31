import React, { useCallback, useMemo } from 'react';
import ReactFlow, {
  Background,
  Controls,
  MiniMap,
  ReactFlowProvider,
  Node,
  Edge,
  NodeTypes,
  MarkerType,
  Position,
} from 'reactflow';
import 'reactflow/dist/style.css';

export type GraphPayload = {
  nodes: any[];
  edges: any[];
  meta?: {
    initial_marking?: string;
    current_marking?: string;
    counts?: {
      places?: number;
      transitions?: number;
    };
  };
};

// Color mapping for workflow states
const stateColors: Record<string, string> = {
  draft: '#6b7280', // gray
  pending: '#eab308', // yellow
  submitted: '#3b82f6', // blue
  confirmed: '#3b82f6', // blue
  under_review: '#eab308', // yellow
  in_review: '#eab308', // yellow
  approved: '#22c55e', // green
  rejected: '#ef4444', // red
  cancelled: '#ef4444', // red
  completed: '#22c55e', // green
  in_progress: '#a855f7', // purple
  processing: '#a855f7', // purple
  shipped: '#6366f1', // indigo
  published: '#22c55e', // green
  archived: '#6b7280', // gray
  resolved: '#22c55e', // green
  closed: '#6b7280', // gray
};

// Custom node component for workflow places
const WorkflowNode = ({ data }: { data: any }) => {
  const color = data.meta?.color || stateColors[data.label.toLowerCase().replace(/\s+/g, '_')] || '#3b82f6';
  const isInitial = data.isInitial;
  const isCurrent = data.isCurrent;

  return (
    <div
      className="px-6 py-4 rounded-xl border-2 shadow-lg bg-white min-w-40 transition-all hover:shadow-xl"
      style={{
        borderColor: color,
        ...(isCurrent && {
          boxShadow: `0 0 0 3px ${color}40`,
          transform: 'scale(1.05)',
        }),
      }}
    >
      <div className="flex items-center gap-2 mb-1">
        {isInitial && (
          <div className="w-2 h-2 rounded-full bg-green-500" title="Initial state"></div>
        )}
        {isCurrent && (
          <div className="w-2 h-2 rounded-full animate-pulse" style={{ backgroundColor: color }}></div>
        )}
        <div className="text-sm font-semibold text-gray-900">{data.label}</div>
      </div>
      {data.meta?.icon && (
        <div className="text-xs text-gray-500 mt-1">
          {data.meta.icon}
        </div>
      )}
    </div>
  );
};

const nodeTypes: NodeTypes = {
  workflow: WorkflowNode,
};

export function ReactFlowCanvas({ graph }: { graph: GraphPayload }) {
  // Transform nodes to include metadata flags
  const nodes: Node[] = useMemo(() => {
    if (!graph?.nodes) return [];

    return graph.nodes.map((node, index) => ({
      ...node,
      type: 'workflow',
      data: {
        ...node.data,
        isInitial: node.id === graph.meta?.initial_marking,
        isCurrent: node.id === graph.meta?.current_marking,
      },
      sourcePosition: Position.Right,
      targetPosition: Position.Left,
    }));
  }, [graph]);

  // Transform edges with better styling
  const edges: Edge[] = useMemo(() => {
    if (!graph?.edges) return [];

    return graph.edges.map((edge) => {
      const roles = edge.data?.meta?.roles || [];
      const label = edge.data?.meta?.label || edge.label;

      return {
        ...edge,
        type: 'smoothstep',
        animated: edge.animated || false,
        label: label,
        labelStyle: {
          fill: '#374151',
          fontWeight: 600,
          fontSize: 12,
        },
        labelBgStyle: {
          fill: '#ffffff',
          fillOpacity: 0.9,
        },
        labelBgPadding: [8, 4] as [number, number],
        labelBgBorderRadius: 4,
        style: {
          strokeWidth: 2,
          stroke: '#94a3b8',
        },
        markerEnd: {
          type: MarkerType.ArrowClosed,
          width: 20,
          height: 20,
          color: '#94a3b8',
        },
      };
    });
  }, [graph]);

  const onNodeClick = useCallback((event: React.MouseEvent, node: Node) => {
    console.log('Node clicked:', node);
  }, []);

  const onEdgeClick = useCallback((event: React.MouseEvent, edge: Edge) => {
    console.log('Edge clicked:', edge);
  }, []);

  if (!nodes.length) {
    return (
      <div className="w-full h-full flex items-center justify-center bg-gray-50 rounded-lg border border-gray-200">
        <div className="text-center">
          <div className="text-gray-400 mb-2">
            <svg className="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p className="text-gray-500 text-sm font-medium">No workflow data available</p>
          <p className="text-gray-400 text-xs mt-1">Add places and transitions to visualize the workflow</p>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full h-full rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-gray-50">
      <ReactFlowProvider>
        <ReactFlow
          nodes={nodes}
          edges={edges}
          nodeTypes={nodeTypes}
          onNodeClick={onNodeClick}
          onEdgeClick={onEdgeClick}
          fitView
          fitViewOptions={{
            padding: 0.2,
            minZoom: 0.5,
            maxZoom: 1.5,
          }}
          minZoom={0.1}
          maxZoom={4}
          defaultEdgeOptions={{
            type: 'smoothstep',
            animated: false,
          }}
          className="bg-linear-to-br from-gray-50 to-white"
        >
          <Background
            gap={20}
            size={1}
            color="#e5e7eb"
            className="bg-white"
          />
          <Controls
            className="bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden"
            showZoom={true}
            showFitView={true}
            showInteractive={true}
          />
          <MiniMap
            className="bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden"
            nodeColor={(node) => {
              const label = node.data?.label?.toLowerCase().replace(/\s+/g, '_');
              return node.data?.meta?.color || stateColors[label] || '#3b82f6';
            }}
            maskColor="rgba(0, 0, 0, 0.05)"
            nodeBorderRadius={12}
          />
        </ReactFlow>
      </ReactFlowProvider>
    </div>
  );
}
