import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
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
  ConnectionLineType,
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

  // Store instance globally for fullscreen handler
  const handleInit = useCallback((instance: any) => {
    setReactFlowInstance(instance);
    (window as any).reactFlowInstance = instance;
  }, []);

  // Initialize nodes and edges from config
  const initialGraph = useMemo(() => {
    if (initialConfig) {
      return parseWorkflowToGraph(initialConfig);
    }
    return { nodes: [], edges: [] };
  }, [initialConfig]);

  const [nodes, setNodes, onNodesChange] = useNodesState(initialGraph.nodes);
  const [edges, setEdges, onEdgesChange] = useEdgesState(initialGraph.edges);

  // Handle window resize to reposition panels and fit view
  useEffect(() => {
    const handleResize = () => {
      if (reactFlowInstance) {
        // Small delay to ensure layout has settled
        setTimeout(() => {
          reactFlowInstance.fitView({ padding: 0.3, duration: 300 });
        }, 100);
      }
    };

    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, [reactFlowInstance]);

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
        type: 'default', // Use default bezier curves
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
        onInit={handleInit}
        onDrop={onDrop}
        onDragOver={onDragOver}
        nodeTypes={nodeTypes}
        isValidConnection={isValidConnection}
        fitView
        fitViewOptions={{ padding: 0.3, maxZoom: 1 }}
        minZoom={0.1}
        maxZoom={2}
        defaultEdgeOptions={{
          type: 'default', // Bezier curves naturally avoid overlap better
          markerEnd: {
            type: MarkerType.ArrowClosed,
            width: 20,
            height: 20,
            color: '#64748b',
          },
          animated: false,
          style: {
            strokeWidth: 2,
            stroke: '#64748b',
          },
        }}
        nodesDraggable={true}
        nodesConnectable={true}
        elementsSelectable={true}
        snapToGrid={true}
        snapGrid={[15, 15]}
        connectionLineStyle={{
          strokeWidth: 2,
          stroke: '#64748b',
        }}
        connectionLineType={ConnectionLineType.Bezier}
        className="bg-linear-to-br from-slate-50 via-gray-50 to-slate-100"
      >
        <Background
          gap={16}
          size={1}
          color="#cbd5e1"
          style={{ opacity: 0.4 }}
        />
        <Controls position="top-right" />
        <MiniMap
          position="bottom-left"
          nodeColor={(node) => {
            const kind = (node.data as DesignerNodeData)?.kind;
            return kind === 'place' ? '#3b82f6' : '#f97316';
          }}
          maskColor="rgba(0, 0, 0, 0.08)"
          nodeStrokeWidth={3}
          style={{ marginBottom: '8px', marginLeft: '8px' }}
          className="bg-white! border! border-gray-200! rounded-lg! shadow-lg!"
        />

        {/* Toolbar Panel */}
        <Panel position="top-left" className="bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-xl border border-gray-200">
          <div className="flex flex-col gap-3">
            <div className="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">
              Components
            </div>
            <div className="flex gap-2">
              {/* Add Place */}
              <div
                className="group flex items-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md hover:shadow-lg cursor-move transition-all"
                onDragStart={(event) => {
                  event.dataTransfer.setData('application/reactflow', 'place');
                  event.dataTransfer.effectAllowed = 'move';
                }}
                draggable
                title="Drag to canvas to add a new place (state)"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span className="text-sm font-semibold">Place</span>
              </div>

              {/* Add Transition */}
              <div
                className="group flex items-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg shadow-md hover:shadow-lg cursor-move transition-all"
                onDragStart={(event) => {
                  event.dataTransfer.setData('application/reactflow', 'transition');
                  event.dataTransfer.effectAllowed = 'move';
                }}
                draggable
                title="Drag to canvas to add a new transition (action)"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span className="text-sm font-semibold">Transition</span>
              </div>
            </div>

            <div className="border-t border-gray-200 pt-3">
              <button
                onClick={exportWorkflow}
                className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-md hover:shadow-lg transition-all font-semibold text-sm"
                title="Export workflow configuration"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Config
              </button>
            </div>
          </div>
        </Panel>

        {/* Keyboard Shortcuts Info */}
        <Panel position="bottom-right" className="bg-white/95 backdrop-blur-sm px-4 py-3 rounded-lg shadow-lg border border-gray-200 text-xs mb-2 mr-2">
          <div className="space-y-1.5 text-gray-600 min-w-[180px]">
            <div className="font-semibold text-gray-700 mb-2 flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Shortcuts
            </div>
            <div className="flex items-center gap-2">
              <kbd className="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono font-semibold">Del</kbd>
              <span>Remove</span>
            </div>
            <div className="flex items-center gap-2">
              <kbd className="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono font-semibold">Esc</kbd>
              <span>Exit fullscreen</span>
            </div>
            <div className="flex items-center gap-1.5">
              <kbd className="px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-mono font-semibold">⌘</kbd>
              <span>+</span>
              <span className="text-gray-500">Scroll</span>
              <span className="text-gray-400">→</span>
              <span>Zoom</span>
            </div>
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
