import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { PlaceData } from '../types';

export function PlaceNode({ data }: NodeProps<PlaceData>) {
  const color = data.meta?.color || '#3b82f6';
  const isInitial = data.isInitial;

  return (
    <div
      className="px-6 py-4 rounded-xl border-2 shadow-lg bg-white min-w-40 transition-all hover:shadow-xl cursor-pointer"
      style={{
        borderColor: color,
        ...(isInitial && {
          boxShadow: `0 0 0 3px ${color}40`,
        }),
      }}
    >
      <Handle
        type="target"
        position={Position.Left}
        className="w-3 h-3 !bg-gray-400"
        isConnectable={true}
      />
      <div className="flex items-center gap-2 mb-1">
        {isInitial && (
          <div className="w-2 h-2 rounded-full bg-green-500" title="Initial state"></div>
        )}
        <div className="text-sm font-semibold text-gray-900">{data.label}</div>
      </div>
      {data.meta?.icon && (
        <div className="text-xs text-gray-500 mt-1">
          {String(data.meta.icon)}
        </div>
      )}
      <Handle
        type="source"
        position={Position.Right}
        className="w-3 h-3 !bg-gray-400"
        isConnectable={true}
      />
    </div>
  );
}
