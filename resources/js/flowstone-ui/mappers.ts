import { Edge, Node, Position } from 'reactflow';
import { WorkflowConfig, DesignerNodeData, WorkflowType } from './types';

export function compileGraphToWorkflow(nodes: Node[], edges: Edge[]): WorkflowConfig {
  const places = nodes
    .filter(n => (n.data as DesignerNodeData)?.kind === 'place')
    .reduce<Record<string, unknown>>((acc, n) => {
      const data = n.data as DesignerNodeData;
      acc[data.key] = { ...(data.meta ?? {}), isInitial: !!(data as any).isInitial };
      return acc;
    }, {});

  const transitionsNodes = nodes.filter(n => (n.data as DesignerNodeData)?.kind === 'transition');
  const placeNodes = nodes.filter(n => (n.data as DesignerNodeData)?.kind === 'place');

  let transitions: Record<string, any> = {};

  // Check if this is a state machine (direct place-to-place edges with labels)
  const hasTransitionNodes = transitionsNodes.length > 0;

  if (hasTransitionNodes) {
    // Workflow type: transitions are nodes
    transitions = transitionsNodes.reduce<Record<string, any>>((acc, t) => {
      const data = t.data as DesignerNodeData;
      const incomingPlaces = edges
        .filter(e => e.target === t.id)
        .map(e => e.source)
        .map(srcId => nodes.find(n => n.id === srcId))
        .filter(Boolean)
        .filter(n => (n!.data as DesignerNodeData)?.kind === 'place')
        .map(n => (n!.data as DesignerNodeData).key);

      const outgoingPlaces = edges
        .filter(e => e.source === t.id)
        .map(e => e.target)
        .map(tgtId => nodes.find(n => n.id === tgtId))
        .filter(Boolean)
        .filter(n => (n!.data as DesignerNodeData)?.kind === 'place')
        .map(n => (n!.data as DesignerNodeData).key);

      const to = outgoingPlaces[0];

      acc[data.key] = {
        from: incomingPlaces,
        to,
        metadata: { ...(data.meta ?? {}) },
      };
      return acc;
    }, {});
  } else {
    // State Machine: transitions are edge labels
    edges.forEach(edge => {
      const sourceNode = nodes.find(n => n.id === edge.source);
      const targetNode = nodes.find(n => n.id === edge.target);

      if (sourceNode && targetNode && edge.label) {
        const transitionKey = edge.data?.transitionKey || edge.label;
        const fromKey = (sourceNode.data as DesignerNodeData).key;
        const toKey = (targetNode.data as DesignerNodeData).key;

        if (!transitions[transitionKey]) {
          transitions[transitionKey] = {
            from: [fromKey],
            to: toKey,
            metadata: edge.data?.metadata || {},
          };
        } else {
          // Add to existing from array if not already there
          if (!transitions[transitionKey].from.includes(fromKey)) {
            transitions[transitionKey].from.push(fromKey);
          }
        }
      }
    });
  }

  return {
    type: hasTransitionNodes ? 'workflow' : 'state_machine',
    places,
    transitions,
    metadata: {},
  };
}

export function parseWorkflowToGraph(
  config: WorkflowConfig,
  workflowType: WorkflowType = 'workflow'
): { nodes: Node[]; edges: Edge[] } {
  const placeIdByKey = new Map<string, string>();
  const transitionIdByKey = new Map<string, string>();
  const nodes: Node[] = [];
  const edges: Edge[] = [];

  // Set default dimensions for ELK layout
  const placeNodeDimensions = { width: 96, height: 96 }; // w-24 h-24 (fixed size)

  // Helper function to calculate transition node width based on text length
  const calculateTransitionWidth = (label: string): number => {
    const minWidth = 96; // min-w-24
    const charWidth = 8; // approximate width per character
    const padding = 32; // px-4 on both sides
    const calculatedWidth = label.length * charWidth + padding;
    return Math.max(minWidth, Math.min(calculatedWidth, 300)); // max 300px
  };

  // Handle places - they can be in different formats
  const places = config.places || {};
  const placeEntries = Object.entries(places);

  // Horizontal layout: places arranged left to right with more spacing
  placeEntries.forEach(([key, placeConfig], idx) => {
    const id = `place-${key}`;
    placeIdByKey.set(key, id);

    // Handle different place config formats
    let meta: any = {};
    let isInitial = false;

    if (typeof placeConfig === 'object' && placeConfig !== null) {
      // Check if it has metadata nested (seeder format)
      if ('metadata' in placeConfig) {
        meta = (placeConfig as any).metadata || {};
        isInitial = key === config.initial_marking; // Use initial_marking from config
      } else {
        // Direct metadata format
        meta = placeConfig;
        isInitial = !!(placeConfig as any).isInitial;
      }
    }

    // Initial position (will be layouted by ELK)
    nodes.push({
      id,
      type: 'place',
      position: { x: 0, y: 0 },
      width: placeNodeDimensions.width,
      height: placeNodeDimensions.height,
      data: {
        kind: 'place',
        key,
        label: key,
        isInitial,
        meta
      },
    });
  });

  // Handle transitions - different rendering based on workflow type
  const transitions = config.transitions || {};
  const transitionEntries = Object.entries(transitions);

  if (workflowType === 'state_machine') {
    // State Machine: Direct edges between places with transition labels
    transitionEntries.forEach(([tKey, def]) => {
      const fromPlaces = (def as any).from || [];
      const toPlace = (def as any).to;
      const meta = (typeof def === 'object' && def !== null && 'metadata' in def)
        ? (def as any).metadata || {}
        : {};

      // Create direct edges from each source place to target place
      fromPlaces.forEach((fromKey: string, idx: number) => {
        const fromId = placeIdByKey.get(fromKey);
        const toId = placeIdByKey.get(toPlace);

        if (fromId && toId) {
          edges.push({
            id: `e-${fromId}-${toId}-${tKey}`,
            source: fromId,
            target: toId,
            label: tKey, // Transition name as edge label
            type: 'custom',
            animated: false,
            data: {
              transitionKey: tKey,
              metadata: meta,
            },
            style: {
              strokeWidth: 2,
              stroke: '#64748b',
            },
          });
        }
      });
    });
  } else {
    // Workflow: Use transition nodes (squares) between places
    transitionEntries.forEach(([tKey, def], idx) => {
      const tId = `transition-${tKey}`;
      transitionIdByKey.set(tKey, tId);

      // Handle transition metadata
      let meta: any = {};
      if (typeof def === 'object' && def !== null && 'metadata' in def) {
        meta = (def as any).metadata || {};
      }

      // Get connected places
      const fromPlaces = (def as any).from || [];
      const toPlace = (def as any).to;

      // Create transition node with calculated width based on label length
      const transitionWidth = calculateTransitionWidth(tKey);
      nodes.push({
        id: tId,
        type: 'transition',
        position: { x: 0, y: 0 },
        width: transitionWidth,
        height: 96, // min-h-24 (can grow vertically if needed)
        data: {
          kind: 'transition',
          key: tKey,
          label: tKey,
          meta
        },
      });

      // Handle from/to connections
      fromPlaces.forEach((pKey: string, pIdx: number) => {
        const pId = placeIdByKey.get(pKey);
        if (pId) {
          edges.push({
            id: `e-${pId}-${tId}-${pIdx}`,
            source: pId,
            target: tId,
            type: 'custom',
            animated: false,
            style: {
              strokeWidth: 2,
              stroke: '#64748b',
            },
          });
        }
      });

      if (toPlace) {
        const toId = placeIdByKey.get(toPlace);
        if (toId) {
          edges.push({
            id: `e-${tId}-${toId}`,
            source: tId,
            target: toId,
            type: 'custom',
            animated: false,
            style: {
              strokeWidth: 2,
              stroke: '#64748b',
            },
          });
        }
      }
    });
  }

  return { nodes, edges };
}
