import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { TransitionData } from '../types';

/**
 * TransitionHandleNode - For State Machine type
 * Small invisible node for connecting places in state machines
 */
export function TransitionHandleNode({ data, selected }: NodeProps<TransitionData>) {
  return (
    <div className="relative">
      {/* Tiny invisible node */}
      <div
        className={`w-2 h-2 rounded-full ${selected ? 'bg-purple-500' : 'bg-gray-300'}`}
        title={data.label}
      >
        {/* Connection Handles */}
        <Handle
          type="target"
          position={Position.Left}
          className="w-2! h-2! bg-purple-500!"
        />
        <Handle
          type="source"
          position={Position.Right}
          className="w-2! h-2! bg-purple-500!"
        />
      </div>
    </div>
  );
}
