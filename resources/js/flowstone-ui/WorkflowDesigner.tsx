import React, { useCallback, useMemo, useRef, useState } from 'react';
import {
  ReactFlow,
  Node,
  Edge,
  addEdge,
  Connection,
  useNodesState,
  useEdgesState,
  Controls,
  MiniMap,
  Background,
  ReactFlowProvider,
  Panel,
  useReactFlow,
  NodeTypes,
  MarkerType,
} from 'reactflow';
import 'reactflow/dist/style.css';

import { PlaceNode } from './nodes/PlaceNode';
import { TransitionNode } from './nodes/TransitionNode';
import { DesignerNodeData, WorkflowConfig } from './types';
import { compileGraphToWorkflow, parseWorkflowToGraph } from './mappers';

const nodeTypes: NodeTypes = {
  place: PlaceNode,
  transition: TransitionNode,
};

interface WorkflowDesignerProps {
  initialConfig?: WorkflowConfig;
  onChange?: (config: WorkflowConfig) => void;
}

function WorkflowDesignerInner({ initialConfig, onChange }: WorkflowDesignerProps) {
  const reactFlowWrapper = useRef<HTMLDivElement>(null);
  const [reactFlowInstance, setReactFlowInstance] = useState<any>(null);

  // Initialize nodes and edges from config
  const initialGraph = useMemo(() => {
    if (initialConfig) {
      return parseWorkflowToGraph(initialConfig);
    }
    return { nodes: [], edges: [] };
  }, [initialConfig]);

  const [nodes, setNodes, onNodesChange] = useNodesState(initialGraph.nodes);
  const [edges, setEdges, onEdgesChange] = useEdgesState(initialGraph.edges);

  // Create a map for quick node lookup
  const nodesById = useMemo(() => {
    const map = new Map<string, Node>();
    nodes.forEach(node => map.set(node.id, node));
    return map;
  }, [nodes]);

  // Validate connections: only place -> transition or transition -> place
  const isValidConnection = useCallback((connection: Connection) => {
    const sourceNode = nodesById.get(connection.source!);
    const targetNode = nodesById.get(connection.target!);

    if (!sourceNode || !targetNode) return false;

    const sourceKind = (sourceNode.data as DesignerNodeData)?.kind;
    const targetKind = (targetNode.data as DesignerNodeData)?.kind;

    return (sourceKind === 'place' && targetKind === 'transition') ||
           (sourceKind === 'transition' && targetKind === 'place');
  }, [nodesById]);

  const onConnect = useCallback((params: Connection) => {
    if (isValidConnection(params)) {
      setEdges((eds) => addEdge({
        ...params,
        type: 'smoothstep',
        data: { arc: (nodesById.get(params.source!)?.data as DesignerNodeData)?.kind === 'place' ? 'in' : 'out' },
      }, eds));
    }
  }, [setEdges, isValidConnection, nodesById]);

  // Add new node
  const addNode = useCallback((type: 'place' | 'transition', position: { x: number; y: number }) => {
    const id = `${type}-${Date.now()}`;
    const key = `${type}_${nodes.filter(n => (n.data as DesignerNodeData)?.kind === type).length + 1}`;

    const newNode: Node = {
      id,
      type,
      position,
      data: {
        kind: type,
        key,
        label: key,
        ...(type === 'place' ? { meta: { color: '#3b82f6' } } : { meta: {} }),
      } as DesignerNodeData,
    };

    setNodes((nds) => [...nds, newNode]);
  }, [setNodes, nodes]);

  // Delete selected nodes and edges
  const onNodesDelete = useCallback((deletedNodes: Node[]) => {
    setNodes((nds) => nds.filter((n) => !deletedNodes.some((dn) => dn.id === n.id)));
  }, [setNodes]);

  const onEdgesDelete = useCallback((deletedEdges: Edge[]) => {
    setEdges((eds) => eds.filter((e) => !deletedEdges.some((de) => de.id === e.id)));
  }, [setEdges]);

  // Handle drag over for drop zones
  const onDragOver = useCallback((event: React.DragEvent) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
  }, []);

  // Handle drop to add new nodes
  const onDrop = useCallback((event: React.DragEvent) => {
    event.preventDefault();

    if (!reactFlowWrapper.current || !reactFlowInstance) return;

    const reactFlowBounds = reactFlowWrapper.current.getBoundingClientRect();
    const type = event.dataTransfer.getData('application/reactflow');

    if (typeof type === 'undefined' || !type) return;

    const position = reactFlowInstance.screenToFlowPosition({
      x: event.clientX - reactFlowBounds.left,
      y: event.clientY - reactFlowBounds.top,
    });

    addNode(type as 'place' | 'transition', position);
  }, [reactFlowInstance, addNode]);

  // Export current graph to workflow config
  const exportWorkflow = useCallback(() => {
    const config = compileGraphToWorkflow(nodes, edges);
    onChange?.(config);
    return config;
  }, [nodes, edges, onChange]);

  return (
    <div className="w-full h-full relative" ref={reactFlowWrapper}>
      <ReactFlow
        nodes={nodes}
        edges={edges}
        onNodesChange={onNodesChange}
        onEdgesChange={onEdgesChange}
        onConnect={onConnect}
        onNodesDelete={onNodesDelete}
        onEdgesDelete={onEdgesDelete}
        onInit={setReactFlowInstance}
        onDrop={onDrop}
        onDragOver={onDragOver}
        nodeTypes={nodeTypes}
        isValidConnection={isValidConnection}
        fitView
        fitViewOptions={{ padding: 0.2 }}
        minZoom={0.1}
        maxZoom={4}
        defaultEdgeOptions={{
          type: 'smoothstep',
          markerEnd: { type: MarkerType.ArrowClosed },
        }}
        className="bg-gray-50"
      >
        <Background gap={20} size={1} color="#e5e7eb" />
        <Controls />
        <MiniMap
          nodeColor={(node) => {
            const kind = (node.data as DesignerNodeData)?.kind;
            return kind === 'place' ? '#3b82f6' : '#fb923c';
          }}
          maskColor="rgba(0, 0, 0, 0.05)"
          nodeBorderRadius={8}
        />

        {/* Toolbar Panel */}
        <Panel position="top-left" className="bg-white p-4 rounded-lg shadow-lg border">
          <div className="flex gap-2">
            <div
              className="px-3 py-2 bg-blue-500 text-white rounded cursor-move"
              onDragStart={(event) => {
                event.dataTransfer.setData('application/reactflow', 'place');
                event.dataTransfer.effectAllowed = 'move';
              }}
              draggable
            >
              Add Place
            </div>
            <div
              className="px-3 py-2 bg-orange-500 text-white rounded cursor-move"
              onDragStart={(event) => {
                event.dataTransfer.setData('application/reactflow', 'transition');
                event.dataTransfer.effectAllowed = 'move';
              }}
              draggable
            >
              Add Transition
            </div>
            <button
              onClick={exportWorkflow}
              className="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            >
              Export
            </button>
          </div>
        </Panel>
      </ReactFlow>
    </div>
  );
}

export function WorkflowDesigner(props: WorkflowDesignerProps) {
  return (
    <ReactFlowProvider>
      <WorkflowDesignerInner {...props} />
    </ReactFlowProvider>
  );
}
