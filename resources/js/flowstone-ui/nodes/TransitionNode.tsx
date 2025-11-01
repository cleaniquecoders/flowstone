import React from 'react';
import { Handle, Position, NodeProps } from 'reactflow';
import { TransitionData } from '../types';

export function TransitionNode({ data, selected }: NodeProps<TransitionData>) {
  return (
    <div className="relative group">
      {/* Rounded Square Transition Node with elegant styling */}
      <div
        className="w-36 h-36 rounded-2xl border-[3px] shadow-xl hover:shadow-2xl flex flex-col items-center justify-center transition-all duration-200 cursor-pointer relative backdrop-blur-sm"
        style={{
          borderColor: selected ? '#f97316' : '#fb923c',
          background: 'linear-gradient(135deg, #ffffff 0%, #fff7ed 100%)',
          boxShadow: selected
            ? '0 0 0 3px rgba(249, 115, 22, 0.25), 0 20px 25px -5px rgba(0, 0, 0, 0.15)'
            : '0 10px 20px -5px rgba(251, 146, 60, 0.2)',
        }}
      >
        {/* Icon Container with gradient background */}
        <div
          className="w-14 h-14 rounded-xl flex items-center justify-center mb-2 relative"
          style={{
            background: 'linear-gradient(135deg, #fed7aa 0%, #fdba74 100%)',
          }}
        >
          <svg className="w-8 h-8 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2.5">
            <path strokeLinecap="round" strokeLinejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>

        {/* Label */}
        <div className="text-sm font-bold text-gray-800 text-center px-3 leading-tight max-w-full overflow-hidden">
          <div className="truncate">{data.label}</div>
        </div>

        {/* Roles Badge with elegant styling */}
        {data.meta?.roles && data.meta.roles.length > 0 && (
          <div
            className="absolute -top-2 -right-2 bg-linear-to-br from-purple-500 to-purple-600 text-white text-xs px-2.5 py-1 rounded-full shadow-lg ring-2 ring-white font-semibold"
            title={`Roles: ${data.meta.roles.join(', ')}`}
          >
            {data.meta.roles.length} {data.meta.roles.length === 1 ? 'role' : 'roles'}
          </div>
        )}

        {/* Subtle corner decorations */}
        <div className="absolute top-1 left-1 w-2 h-2 bg-orange-300 rounded-full opacity-20"></div>
        <div className="absolute bottom-1 right-1 w-2 h-2 bg-orange-300 rounded-full opacity-20"></div>

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
      <div className="absolute -bottom-10 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none z-50">
        <div className="bg-gray-900/90 backdrop-blur-sm text-white text-xs py-1.5 px-3 rounded-lg shadow-lg max-w-xs">
          <div className="font-medium">{data.key}</div>
          {data.meta?.roles && data.meta.roles.length > 0 && (
            <div className="text-purple-300 mt-0.5 text-[10px]">
              {data.meta.roles.join(', ')}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
