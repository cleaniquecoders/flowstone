import React from 'react';
import { Handle, Position, NodeProps, NodeResizer } from 'reactflow';
import { TransitionData } from '../types';

export function TransitionNode({ data, selected }: NodeProps<TransitionData>) {
  const hasMetadata = data.meta && Object.keys(data.meta).length > 0;

  const handleMetadataClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    console.log('Metadata button clicked for transition:', data.label);
    console.log('Transition ID:', data.id);
    console.log('Livewire available?', !!window.Livewire);

    if (data.id) {
      console.log('Dispatching open-transition-metadata-modal with transitionId:', data.id);
      window.Livewire?.dispatch('open-transition-metadata-modal', { transitionId: data.id });
    } else {
      console.error('No ID found for transition:', data.label);
    }
  };

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

        {/* Metadata Button - positioned outside node */}
        <button
          onClick={handleMetadataClick}
          onMouseDown={(e) => e.stopPropagation()}
          className={`absolute -top-4 -right-4 w-7 h-7 rounded-full border-2 border-white shadow-lg flex items-center justify-center transition-all hover:scale-110 cursor-pointer z-10 nodrag nopan ${
            hasMetadata ? 'bg-orange-600 hover:bg-orange-700' : 'bg-gray-400 hover:bg-gray-500'
          }`}
          title={hasMetadata ? 'Edit metadata' : 'Add metadata'}
        >
          <svg className="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
        </button>

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
