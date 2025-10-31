export type NodeKind = 'place' | 'transition';

export type PlaceData = {
  kind: 'place';
  key: string;
  label: string;
  isInitial?: boolean;
  meta?: { color?: string; [k: string]: unknown };
};

export type TransitionData = {
  kind: 'transition';
  key: string;
  label: string;
  meta?: { roles?: string[]; guard?: string; [k: string]: unknown };
};

export type DesignerNodeData = PlaceData | TransitionData;

export type WorkflowConfig = {
  type: 'state_machine' | 'workflow';
  places: Record<string, unknown>;
  transitions: Record<
    string,
    { from: string[]; to: string; metadata?: Record<string, unknown> }
  >;
  initial_marking?: string;
  marking_store?: unknown;
  supports?: unknown;
  metadata?: Record<string, unknown>;
};

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
