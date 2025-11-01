import React from 'react';
import { createRoot, Root } from 'react-dom/client';
import { ReactFlowCanvas, WorkflowDesigner, type GraphPayload, type WorkflowConfig } from './index';
import { InfoModal } from './components/InfoModal';
import '../../css/flowstone.css';

// Expose a global mount function under window.FlowstoneUI
// Build will bundle React + ReactFlow into a single UMD file.

declare global {
  interface Window {
    FlowstoneUI?: {
      mount: (el: HTMLElement, graph: GraphPayload) => void;
      mountDesigner: (el: HTMLElement, config?: WorkflowConfig, designer?: any, onChange?: (config: WorkflowConfig, designer: any) => void) => void;
      mountInfoModal: (el: HTMLElement, workflowType: string) => void;
    };
  }
}

if (!window.FlowstoneUI) {
  window.FlowstoneUI = { mount: () => {}, mountDesigner: () => {}, mountInfoModal: () => {} } as any;
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

(window.FlowstoneUI as any).mountDesigner = (el: HTMLElement, config?: WorkflowConfig, designer?: any, onChange?: (config: WorkflowConfig, designer: any) => void) => {
  try {
    let root: Root | undefined = (el as any).__flowstone_root__;
    if (!root) {
      root = createRoot(el);
      (el as any).__flowstone_root__ = root;
    }
    // Determine workflow type from config
    const workflowType = config?.type || 'workflow';
    root.render(React.createElement(WorkflowDesigner, {
      initialConfig: config,
      initialDesigner: designer,
      onChange,
      workflowType
    }));
  } catch (e) {
    console.error('FlowstoneUI.mountDesigner error:', e);
    if (el) {
      el.innerHTML = '<div style="padding:12px;color:#b91c1c;">Failed to load Flowstone UI Designer bundle.</div>';
    }
  }
};

(window.FlowstoneUI as any).mountInfoModal = (el: HTMLElement, workflowType: string) => {
  try {
    let root: Root | undefined = (el as any).__flowstone_info_root__;
    if (!root) {
      root = createRoot(el);
      (el as any).__flowstone_info_root__ = root;
    }

    // Handle close by unmounting
    const handleClose = () => {
      if (root) {
        root.unmount();
        (el as any).__flowstone_info_root__ = undefined;
      }
    };

    root.render(React.createElement(InfoModal, {
      isOpen: true,
      workflowType,
      onClose: handleClose
    }));
  } catch (e) {
    console.error('FlowstoneUI.mountInfoModal error:', e);
  }
};
