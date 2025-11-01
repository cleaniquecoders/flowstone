import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { PlaceData } from '../types';

export function PlaceNode({ data, selected }: NodeProps<PlaceData>) {
  const isInitial = data.isInitial;

  return (
    <div className="relative">
      {/* Auto-sized Circular Place Node - Always Blue */}
      <div
        className={`min-w-24 min-h-24 max-w-40 rounded-md border-4 bg-blue-50 flex items-center justify-center transition-all cursor-pointer px-4 py-4 ${
          selected ? 'border-blue-600 shadow-lg ring-2 ring-blue-300' : 'border-blue-500 shadow-md'
        }`}
      >
        {/* Connection Handles - styled via CSS */}
        <Handle
          type="target"
          position={Position.Left}
        />
        <Handle
          type="source"
          position={Position.Right}
        />

        {/* Initial Indicator */}
        {isInitial && (
          <div className="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-md border-2 border-white" />
        )}

        {/* Label */}
        <div className="text-sm font-bold text-blue-900 text-center wrap-break-word leading-tight">
          {data.label}
        </div>
      </div>
    </div>
  );
}
