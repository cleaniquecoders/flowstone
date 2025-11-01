import React from 'react';
import { Handle, Position, NodeProps, NodeResizer } from 'reactflow';
import { TransitionData } from '../types';

export function TransitionNode({ data, selected }: NodeProps<TransitionData>) {
  return (
    <div className="relative w-full h-full">
      {/* Node Resizer - visible when selected, resize from all sides */}
      <NodeResizer
        isVisible={selected}
        minWidth={112}
        minHeight={96}
        handleStyle={{ width: '12px', height: '12px' }}
        lineStyle={{ borderWidth: '2px' }}
      />

      {/* Transition Node with Dashed Border */}
      <div
        className={`w-full h-full rounded-md bg-orange-50 border-4 border-dashed flex items-center justify-center transition-all cursor-pointer px-5 py-4 ${
          selected ? 'border-orange-600 shadow-lg ring-2 ring-orange-300' : 'border-orange-500 shadow-md'
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

        {/* Label - with roles info */}
        <div className="flex flex-col items-center gap-1.5 w-full">
          <div className="text-sm font-bold text-orange-900 text-center wrap-break-word leading-tight w-full">
            {data.label}
          </div>
          {data.meta?.roles && data.meta.roles.length > 0 && (
            <div className="text-xs text-purple-600 font-medium whitespace-nowrap">
              {data.meta.roles.length} role{data.meta.roles.length > 1 ? 's' : ''}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
