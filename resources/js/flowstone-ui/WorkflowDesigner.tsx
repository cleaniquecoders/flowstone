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
  EdgeTypes,
  MarkerType,
  ConnectionLineType,
} from 'reactflow';
import 'reactflow/dist/style.css';

import { PlaceNode } from './nodes/PlaceNode';
import { TransitionNode } from './nodes/TransitionNode';
import { TransitionHandleNode } from './nodes/TransitionHandleNode';
import { CustomEdge } from './edges/CustomEdge';
import { DesignerNodeData, WorkflowConfig, WorkflowType } from './types';
import { compileGraphToWorkflow, parseWorkflowToGraph } from './mappers';
import { getLayoutedElements } from './layoutUtils';

const nodeTypes: NodeTypes = {
  place: PlaceNode,
  transition: TransitionNode,
  transitionHandle: TransitionHandleNode,
};

const edgeTypes: EdgeTypes = {
  custom: CustomEdge,
};

interface WorkflowDesignerProps {
  initialConfig?: WorkflowConfig;
  initialDesigner?: any;
  onChange?: (config: WorkflowConfig, designer: any) => void;
  workflowType?: WorkflowType;
}

function WorkflowDesignerInner({ initialConfig, initialDesigner, onChange, workflowType = 'workflow' }: WorkflowDesignerProps) {
  const reactFlowWrapper = useRef<HTMLDivElement>(null);
  const [reactFlowInstance, setReactFlowInstance] = useState<any>(null);
  const [showToolbar, setShowToolbar] = useState(false);

  // Store instance globally for fullscreen handler
  const handleInit = useCallback((instance: any) => {
    setReactFlowInstance(instance);
    (window as any).reactFlowInstance = instance;
  }, []);

  // Initialize nodes and edges from config with ELK layout
  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);

  // Apply ELK layout when config loads OR load saved designer positions
  useEffect(() => {
    if (initialConfig) {
      const graph = parseWorkflowToGraph(initialConfig, workflowType);

      // Check if we have saved designer positions
      if (initialDesigner?.nodes && initialDesigner.nodes.length > 0) {
        // Use saved positions
        const nodesWithPositions = graph.nodes.map(node => {
          const savedNode = initialDesigner.nodes.find((n: any) => n.id === node.id);
          if (savedNode?.position) {
            return { ...node, position: savedNode.position };
          }
          return node;
        });

        setNodes(nodesWithPositions);
        setEdges(initialDesigner.edges || graph.edges);

        // Restore viewport if saved
        if (initialDesigner.viewport && reactFlowInstance) {
          setTimeout(() => {
            reactFlowInstance.setViewport(initialDesigner.viewport);
          }, 100);
        }
      } else {
        // Use ELK auto-layout for new workflows
        getLayoutedElements(graph.nodes, graph.edges).then(({ nodes: layoutedNodes, edges: layoutedEdges }) => {
          setNodes(layoutedNodes);
          setEdges(layoutedEdges);
        });
      }
    }
  }, [initialConfig, initialDesigner, workflowType, setNodes, setEdges, reactFlowInstance]);

  // Auto-save: Trigger onChange when nodes or edges change
  useEffect(() => {
    if (nodes.length > 0 && onChange) {
      const config = compileGraphToWorkflow(nodes, edges);
      const designerData = {
        nodes: nodes.map(node => ({
          id: node.id,
          position: node.position,
          type: node.type,
        })),
        edges: edges.map(edge => ({
          id: edge.id,
          source: edge.source,
          target: edge.target,
          label: edge.label,
        })),
        viewport: reactFlowInstance?.getViewport() || { x: 0, y: 0, zoom: 1 },
      };
      onChange(config, designerData);
    }
  }, [nodes, edges, reactFlowInstance, onChange]);

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

  // Allow all connections - no restrictions
  const isValidConnection = useCallback((connection: Connection) => {
    const sourceNode = nodesById.get(connection.source!);
    const targetNode = nodesById.get(connection.target!);

    // Just check that both nodes exist
    return !!(sourceNode && targetNode);
  }, [nodesById]);

  const onConnect = useCallback((params: Connection) => {
    if (isValidConnection(params)) {
      const sourceNode = nodesById.get(params.source!);
      const targetNode = nodesById.get(params.target!);

      // For state machine, prompt for transition name
      let edgeLabel = '';
      if (workflowType === 'state_machine') {
        // Direct place to place connection
        const transitionName = prompt('Enter transition name:', `transition_${edges.length + 1}`);
        if (!transitionName) return; // User cancelled
        edgeLabel = transitionName;
      }

      setEdges((eds) => addEdge({
        ...params,
        type: 'custom',
        animated: true,
        label: edgeLabel,
        data: {
          transitionKey: edgeLabel || undefined,
        },
        style: {
          strokeWidth: 2.5,
          stroke: '#64748b',
        },
      }, eds));
    }
  }, [setEdges, isValidConnection, nodesById, workflowType, edges]);  // Add new node
  const addNode = useCallback((type: 'place' | 'transition', position: { x: number; y: number }) => {
    const id = `${type}-${Date.now()}`;
    const key = `${type}_${nodes.filter(n => (n.data as DesignerNodeData)?.kind === type).length + 1}`;

    // Determine node type based on workflow type
    let nodeType = type;
    if (type === 'transition' && workflowType === 'state_machine') {
      nodeType = 'transitionHandle' as any;
    }

    const newNode: Node = {
      id,
      type: nodeType,
      position,
      data: {
        kind: type,
        key,
        label: key,
        ...(type === 'place' ? { meta: { color: '#3b82f6' } } : { meta: {} }),
      } as DesignerNodeData,
    };

    setNodes((nds) => [...nds, newNode]);
  }, [setNodes, nodes, workflowType]);

  // Handle double click to edit node label
  const onNodeDoubleClick = useCallback((event: React.MouseEvent, node: Node) => {
    const data = node.data as DesignerNodeData;
    const newLabel = prompt('Enter new label:', data.label);

    if (newLabel && newLabel.trim()) {
      const trimmedLabel = newLabel.trim();
      setNodes((nds) =>
        nds.map((n) =>
          n.id === node.id
            ? {
                ...n,
                data: {
                  ...n.data,
                  label: trimmedLabel,
                  key: trimmedLabel // Update key to match label for consistency
                }
              }
            : n
        )
      );
    }
  }, [setNodes]);

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

  // Export current graph to workflow config with designer data
  const exportWorkflow = useCallback(() => {
    const config = compileGraphToWorkflow(nodes, edges);

    // Capture designer layout data
    const designerData = {
      nodes: nodes.map(node => ({
        id: node.id,
        position: node.position,
        type: node.type,
      })),
      edges: edges.map(edge => ({
        id: edge.id,
        source: edge.source,
        target: edge.target,
        label: edge.label,
      })),
      viewport: reactFlowInstance?.getViewport() || { x: 0, y: 0, zoom: 1 },
    };

    onChange?.(config, designerData);
    return config;
  }, [nodes, edges, reactFlowInstance, onChange]);

  return (
    <div className="w-full h-full relative" ref={reactFlowWrapper}>
      <ReactFlow
        nodes={nodes}
        edges={edges}
        onNodesChange={onNodesChange}
        onEdgesChange={onEdgesChange}
        onConnect={onConnect}
        onNodeDoubleClick={onNodeDoubleClick}
        onNodesDelete={onNodesDelete}
        onEdgesDelete={onEdgesDelete}
        onInit={handleInit}
        onDrop={onDrop}
        onDragOver={onDragOver}
        nodeTypes={nodeTypes}
        edgeTypes={edgeTypes}
        isValidConnection={isValidConnection}
        fitView
        fitViewOptions={{ padding: 0.3, maxZoom: 1 }}
        minZoom={0.1}
        maxZoom={2}
        defaultEdgeOptions={{
          type: 'custom', // Custom bezier edge with animated dashed lines
          markerEnd: {
            type: MarkerType.ArrowClosed,
            width: 22,
            height: 22,
            color: '#64748b',
          },
          animated: true,
          style: {
            strokeWidth: 2.5,
            stroke: '#64748b',
          },
        }}
        nodesDraggable={true}
        nodesConnectable={true}
        elementsSelectable={true}
        snapToGrid={true}
        snapGrid={[15, 15]}
        connectionLineStyle={{
          strokeWidth: 2.5,
          stroke: '#64748b',
          strokeDasharray: '8, 4',
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

        {/* Toggle Toolbar Button */}
        <Panel position="top-left" className="flex items-start gap-2">
          <button
            onClick={() => setShowToolbar(!showToolbar)}
            className="p-2 bg-white/95 backdrop-blur-sm rounded-lg shadow-lg border border-gray-200 hover:bg-gray-50 transition-all"
            title={showToolbar ? 'Hide toolbar' : 'Show toolbar'}
          >
            <svg className="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              {showToolbar ? (
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
              ) : (
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
              )}
            </svg>
          </button>

          {/* Toolbar Panel */}
          {showToolbar && (
            <div className="bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-xl border border-gray-200">
              <div className="flex flex-col gap-3">
                {/* Workflow Type Indicator */}
                <div className="flex items-center gap-2 pb-2 border-b border-gray-200">
                  <div className={`px-2 py-1 rounded text-xs font-bold ${
                    workflowType === 'state_machine'
                      ? 'bg-purple-100 text-purple-700'
                      : 'bg-blue-100 text-blue-700'
                  }`}>
                    {workflowType === 'state_machine' ? 'State Machine' : 'Workflow'}
                  </div>
                </div>

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

              {/* Add Transition - Only for Workflow type */}
              {workflowType === 'workflow' && (
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
              )}
            </div>

                {/* State Machine Instructions */}
                {workflowType === 'state_machine' && (
                  <div className="text-xs text-gray-600 bg-purple-50 border border-purple-200 rounded-lg p-2 mt-2">
                    <div className="flex items-start gap-2">
                      <svg className="w-4 h-4 text-purple-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span className="leading-relaxed">
                        Connect places directly. You'll be prompted to name the transition.
                      </span>
                    </div>
                  </div>
                )}

                {/* Edit Label Instructions */}
                <div className="text-xs text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-2 mt-2">
                  <div className="flex items-start gap-2">
                    <svg className="w-4 h-4 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span className="leading-relaxed">
                      Double-click any node to edit its label.
                    </span>
                  </div>
                </div>
              </div>
            </div>
          )}
        </Panel>

        {/* Simple Info Panel */}
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
