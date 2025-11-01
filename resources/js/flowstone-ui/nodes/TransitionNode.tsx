import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { TransitionData } from '../types';

export function TransitionNode({ data, selected }: NodeProps<TransitionData>) {
  return (
    <div className="relative inline-block">
      {/* Auto-sized Square Transition Node */}
      <div
        className={`min-w-24 min-h-24 rounded-lg border-4 flex items-center justify-center transition-all cursor-pointer px-4 py-3 ${
          selected ? 'border-orange-600 shadow-lg' : 'border-orange-400 shadow-md'
        } bg-white`}
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

        {/* Label - with roles info */}
        <div className="flex flex-col items-center gap-1">
          <div className="text-sm font-bold text-gray-800 text-center wrap-break-word max-w-xs">
            {data.label}
          </div>
          {data.meta?.roles && data.meta.roles.length > 0 && (
            <div className="text-xs text-purple-600 font-medium">
              {data.meta.roles.length} role{data.meta.roles.length > 1 ? 's' : ''}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
