import React from 'react';
import { createRoot, Root } from 'react-dom/client';
import { ReactFlowCanvas, type GraphPayload } from './ReactFlowCanvas';

// Expose a global mount function under window.FlowstoneUI
// Build will bundle React + ReactFlow into a single UMD file.

declare global {
  interface Window {
    FlowstoneUI?: {
      mount: (el: HTMLElement, graph: GraphPayload) => void;
    };
  }
}

if (!window.FlowstoneUI) {
  window.FlowstoneUI = { mount: () => {} } as any;
}

(window.FlowstoneUI as any).mount = (el: HTMLElement, graph: GraphPayload) => {
  try {
    let root: Root | undefined = (el as any).__flowstone_root__;
    if (!root) {
      root = createRoot(el);
      (el as any).__flowstone_root__ = root;
    }
    root.render(React.createElement(ReactFlowCanvas, { graph }));
  } catch (e) {
    console.error('FlowstoneUI.mount error:', e);
    if (el) {
      el.innerHTML = '<div style="padding:12px;color:#b91c1c;">Failed to load Flowstone UI bundle.</div>';
    }
  }
};
