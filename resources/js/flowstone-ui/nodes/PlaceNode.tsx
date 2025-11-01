import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { PlaceData } from '../types';

export function PlaceNode({ data }: NodeProps<PlaceData>) {
  const color = data.meta?.color || '#3b82f6';
  const isInitial = data.isInitial;

  return (
    <div className="relative group">
      {/* Circular Place Node */}
      <div
        className="w-32 h-32 rounded-full border-4 shadow-lg bg-gradient-to-br from-white to-blue-50 flex flex-col items-center justify-center transition-all hover:shadow-xl cursor-pointer relative"
        style={{
          borderColor: color,
          ...(isInitial && {
            boxShadow: `0 0 0 4px ${color}30, 0 10px 25px -5px rgba(0, 0, 0, 0.1)`,
          }),
        }}
      >
        {/* Initial State Indicator */}
        {isInitial && (
          <div className="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-lg" title="Initial state">
            <svg className="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
            </svg>
          </div>
        )}

        {/* Place Icon */}
        <div className="mb-2" style={{ color }}>
          <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>

        {/* Label */}
        <div className="text-sm font-bold text-gray-900 text-center px-2 leading-tight">
          {data.label}
        </div>

        {/* Connection Handles */}
        <Handle
          type="target"
          position={Position.Top}
          className="w-3 h-3 !bg-blue-500 !border-2 !border-white"
          isConnectable={true}
          style={{ top: -6 }}
        />
        <Handle
          type="target"
          position={Position.Left}
          className="w-3 h-3 !bg-blue-500 !border-2 !border-white"
          isConnectable={true}
          style={{ left: -6 }}
        />
        <Handle
          type="source"
          position={Position.Right}
          className="w-3 h-3 !bg-blue-500 !border-2 !border-white"
          isConnectable={true}
          style={{ right: -6 }}
        />
        <Handle
          type="source"
          position={Position.Bottom}
          className="w-3 h-3 !bg-blue-500 !border-2 !border-white"
          isConnectable={true}
          style={{ bottom: -6 }}
        />
      </div>

      {/* Hover Tooltip */}
      <div className="absolute -bottom-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
        <div className="bg-gray-900 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
          Place: {data.key}
        </div>
      </div>
    </div>
  );
}
