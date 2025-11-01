import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { PlaceData } from '../types';

export function PlaceNode({ data, selected }: NodeProps<PlaceData>) {
  const isInitial = data.isInitial;

  return (
    <div className="relative">
      {/* Simple Circular Place Node */}
      <div
        className={`w-24 h-24 rounded-full border-4 flex items-center justify-center transition-all cursor-pointer ${
          selected ? 'border-blue-600 shadow-lg' : 'border-blue-400 shadow-md'
        } ${isInitial ? 'bg-blue-100' : 'bg-white'}`}
      >
        {/* Connection Handles */}
        <Handle
          type="target"
          position={Position.Left}
          className="w-3! h-3! bg-blue-500! border-2! border-white!"
        />
        <Handle
          type="source"
          position={Position.Right}
          className="w-3! h-3! bg-blue-500! border-2! border-white!"
        />

        {/* Initial Indicator */}
        {isInitial && (
          <div className="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white" />
        )}

        {/* Label */}
        <div className="text-sm font-bold text-gray-800 text-center px-2 wrap-break-word">
          {data.label}
        </div>
      </div>
    </div>
  );
}
