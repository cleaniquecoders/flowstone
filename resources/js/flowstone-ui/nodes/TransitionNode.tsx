import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { TransitionData } from '../types';

export function TransitionNode({ data }: NodeProps<TransitionData>) {
  return (
    <div className="px-4 py-3 rounded-lg border-2 border-orange-400 shadow-lg bg-white min-w-32 transition-all hover:shadow-xl cursor-pointer">
      <Handle
        type="target"
        position={Position.Top}
        className="w-3 h-3 bg-orange-400"
        style={{ backgroundColor: '#fb923c' }}
        isConnectable={true}
      />
      <div className="text-center">
        <div className="text-xs font-semibold text-gray-900 mb-1">{data.label}</div>
        {data.meta?.roles && data.meta.roles.length > 0 && (
          <div className="text-xs text-gray-500">
            {data.meta.roles.join(', ')}
          </div>
        )}
      </div>
      <Handle
        type="source"
        position={Position.Bottom}
        className="w-3 h-3 bg-orange-400"
        style={{ backgroundColor: '#fb923c' }}
        isConnectable={true}
      />
    </div>
  );
}
