import { Edge, Node } from 'reactflow';
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
  const nodes: Node[] = [];
  const edges: Edge[] = [];

  // Handle places - they can be in different formats
  const places = config.places || {};
  Object.entries(places).forEach(([key, placeConfig], idx) => {
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

    nodes.push({
      id,
      type: 'place',
      position: { x: 100 + idx * 120, y: 100 },
      data: {
        kind: 'place',
        key,
        label: key,
        isInitial,
        meta
      },
    });
  });

  // Handle transitions
  const transitions = config.transitions || {};
  Object.entries(transitions).forEach(([tKey, def], idx) => {
    const tId = `transition-${tKey}`;

    // Handle transition metadata
    let meta: any = {};
    if (typeof def === 'object' && def !== null && 'metadata' in def) {
      meta = (def as any).metadata || {};
    }

    nodes.push({
      id: tId,
      type: 'transition',
      position: { x: 100 + idx * 160, y: 300 },
      data: {
        kind: 'transition',
        key: tKey,
        label: tKey,
        meta
      },
    });

    // Handle from/to connections
    const fromPlaces = (def as any).from || [];
    const toPlace = (def as any).to;

    fromPlaces.forEach((pKey: string) => {
      const pId = placeIdByKey.get(pKey);
      if (pId) {
        edges.push({
          id: `e-${pId}-${tId}`,
          source: pId,
          target: tId,
          label: '',
          data: { arc: 'in' },
          type: 'smoothstep',
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
          type: 'smoothstep',
        });
      }
    }
  });

  return { nodes, edges };
}
