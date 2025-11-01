import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { PlaceData } from '../types';

export function PlaceNode({ data, selected }: NodeProps<PlaceData>) {
  const color = data.meta?.color || '#3b82f6';
  const isInitial = data.isInitial;

  return (
    <div className="relative group">
      {/* Circular Place Node with elegant styling */}
      <div
        className="w-36 h-36 rounded-full border-[3px] shadow-xl hover:shadow-2xl flex flex-col items-center justify-center transition-all duration-200 cursor-pointer relative backdrop-blur-sm"
        style={{
          borderColor: selected ? '#3b82f6' : color,
          background: isInitial
            ? 'linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%)'
            : 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
          boxShadow: selected
            ? `0 0 0 3px ${color}40, 0 20px 25px -5px rgba(0, 0, 0, 0.15)`
            : isInitial
            ? `0 0 0 3px ${color}30, 0 10px 20px -5px rgba(59, 130, 246, 0.3)`
            : '0 10px 20px -5px rgba(0, 0, 0, 0.1)',
        }}
      >
        {/* Initial State Glow Indicator */}
        {isInitial && (
          <div
            className="absolute inset-0 rounded-full opacity-20 animate-pulse"
            style={{
              background: `radial-gradient(circle, ${color}40 0%, transparent 70%)`,
            }}
          />
        )}

        {/* Initial State Badge */}
        {isInitial && (
          <div className="absolute -top-2 -right-2 w-7 h-7 bg-linear-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg ring-2 ring-white" title="Initial state">
            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
            </svg>
          </div>
        )}

        {/* Icon Container */}
        <div
          className="w-12 h-12 rounded-full flex items-center justify-center mb-1.5 relative z-10"
          style={{
            backgroundColor: `${color}15`,
            color: color
          }}
        >
          <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2.5">
            <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>

        {/* Label */}
        <div className="text-sm font-bold text-gray-800 text-center px-3 leading-tight relative z-10">
          {data.label}
        </div>

        {/* Connection Handles - Hidden visually but functional */}
        <Handle
          type="target"
          position={Position.Top}
          className="w-2 h-2 bg-slate-400! border-2! border-white! opacity-0 hover:opacity-100! transition-opacity"
          isConnectable={true}
          style={{ top: -4 }}
        />
        <Handle
          type="target"
          position={Position.Left}
          className="w-2 h-2 bg-slate-400! border-2! border-white! opacity-0 hover:opacity-100! transition-opacity"
          isConnectable={true}
          style={{ left: -4 }}
        />
        <Handle
          type="source"
          position={Position.Right}
          className="w-2 h-2 bg-slate-400! border-2! border-white! opacity-0 hover:opacity-100! transition-opacity"
          isConnectable={true}
          style={{ right: -4 }}
        />
        <Handle
          type="source"
          position={Position.Bottom}
          className="w-2 h-2 bg-slate-400! border-2! border-white! opacity-0 hover:opacity-100! transition-opacity"
          isConnectable={true}
          style={{ bottom: -4 }}
        />
      </div>

      {/* Elegant Hover Label */}
      <div className="absolute -bottom-10 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none">
        <div className="bg-gray-900/90 backdrop-blur-sm text-white text-xs py-1.5 px-3 rounded-lg whitespace-nowrap shadow-lg">
          <span className="font-medium">{data.key}</span>
          {isInitial && <span className="ml-2 text-green-400">● Initial</span>}
        </div>
      </div>
    </div>
  );
}
