import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { TransitionData } from '../types';

export function TransitionNode({ data }: NodeProps<TransitionData>) {
  return (
    <div className="relative group">
      {/* Square Transition Node */}
      <div className="w-32 h-32 rounded-lg border-4 border-orange-500 shadow-lg bg-gradient-to-br from-white to-orange-50 flex flex-col items-center justify-center transition-all hover:shadow-xl hover:border-orange-600 cursor-pointer relative">
        {/* Transition Icon */}
        <div className="mb-2 text-orange-600">
          <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>

        {/* Label */}
        <div className="text-sm font-bold text-gray-900 text-center px-2 leading-tight">
          {data.label}
        </div>

        {/* Roles Badge */}
        {data.meta?.roles && data.meta.roles.length > 0 && (
          <div className="absolute -top-2 -right-2 bg-purple-500 text-white text-xs px-2 py-0.5 rounded-full shadow-md" title={`Roles: ${data.meta.roles.join(', ')}`}>
            {data.meta.roles.length}
          </div>
        )}

        {/* Connection Handles */}
        <Handle
          type="target"
          position={Position.Top}
          className="w-3 h-3 bg-orange-500! border-2! border-white!"
          isConnectable={true}
          style={{ top: -6 }}
        />
        <Handle
          type="target"
          position={Position.Left}
          className="w-3 h-3 bg-orange-500! border-2! border-white!"
          isConnectable={true}
          style={{ left: -6 }}
        />
        <Handle
          type="source"
          position={Position.Right}
          className="w-3 h-3 bg-orange-500! border-2! border-white!"
          isConnectable={true}
          style={{ right: -6 }}
        />
        <Handle
          type="source"
          position={Position.Bottom}
          className="w-3 h-3 bg-orange-500! border-2! border-white!"
          isConnectable={true}
          style={{ bottom: -6 }}
        />
      </div>

      {/* Hover Tooltip */}
      <div className="absolute -bottom-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
        <div className="bg-gray-900 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
          Transition: {data.key}
          {data.meta?.roles && data.meta.roles.length > 0 && (
            <span className="ml-1">• {data.meta.roles.join(', ')}</span>
          )}
        </div>
      </div>
    </div>
  );
}
