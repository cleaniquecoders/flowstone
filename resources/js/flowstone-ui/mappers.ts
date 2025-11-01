import { Edge, Node, Position } from 'reactflow';
import { WorkflowConfig, DesignerNodeData } from './types';

export function compileGraphToWorkflow(nodes: Node[], edges: Edge[]): WorkflowConfig {
  const places = nodes
    .filter(n => (n.data as DesignerNodeData)?.kind === 'place')
    .reduce<Record<string, unknown>>((acc, n) => {
      const data = n.data as DesignerNodeData;
      acc[data.key] = { ...(data.meta ?? {}), isInitial: !!(data as any).isInitial };
      return acc;
    }, {});

  const transitionsNodes = nodes.filter(n => (n.data as DesignerNodeData)?.kind === 'transition');

  const transitions = transitionsNodes.reduce<Record<string, any>>((acc, t) => {
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

    // For now, single target place
    const to = outgoingPlaces[0];

    acc[data.key] = {
      from: incomingPlaces,
      to,
      metadata: { ...(data.meta ?? {}) },
    };
    return acc;
  }, {});

  return {
    type: 'state_machine',
    places,
    transitions,
    metadata: {},
  };
}

export function parseWorkflowToGraph(config: WorkflowConfig): { nodes: Node[]; edges: Edge[] } {
  const placeIdByKey = new Map<string, string>();
  const transitionIdByKey = new Map<string, string>();
  const nodes: Node[] = [];
  const edges: Edge[] = [];

  // Handle places - they can be in different formats
  const places = config.places || {};
  const placeEntries = Object.entries(places);

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

    // Horizontal layout: places arranged left to right
    nodes.push({
      id,
      type: 'place',
      position: { x: 100 + idx * 320, y: 200 },
      data: {
        kind: 'place',
        key,
        label: key,
        isInitial,
        meta
      },
    });
  });

  // Handle transitions - positioned between their connected places
  const transitions = config.transitions || {};
  const transitionEntries = Object.entries(transitions);

  transitionEntries.forEach(([tKey, def], idx) => {
    const tId = `transition-${tKey}`;
    transitionIdByKey.set(tKey, tId);

    // Handle transition metadata
    let meta: any = {};
    if (typeof def === 'object' && def !== null && 'metadata' in def) {
      meta = (def as any).metadata || {};
    }

    // Calculate position based on connected places for better horizontal flow
    const fromPlaces = (def as any).from || [];
    const toPlace = (def as any).to;

    let xPos = 100 + idx * 320;
    let yPos = 400; // Below places for vertical spacing

    // Try to position transition between source and target
    if (fromPlaces.length > 0 && toPlace) {
      const fromIdx = placeEntries.findIndex(([k]) => k === fromPlaces[0]);
      const toIdx = placeEntries.findIndex(([k]) => k === toPlace);

      if (fromIdx !== -1 && toIdx !== -1) {
        xPos = 100 + ((fromIdx + toIdx) / 2) * 320;
      }
    }

    nodes.push({
      id: tId,
      type: 'transition',
      position: { x: xPos, y: yPos },
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
          label: '',
          data: { arc: 'in' },
          type: 'default', // Use default bezier for smoother curves
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
          label: '',
          data: { arc: 'out' },
          type: 'default', // Use default bezier for smoother curves
          style: {
            strokeWidth: 2,
            stroke: '#64748b',
          },
        });
      }
    }
  });

  return { nodes, edges };
}
