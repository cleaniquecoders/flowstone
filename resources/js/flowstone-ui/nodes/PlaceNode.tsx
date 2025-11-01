import React from 'react';
import { Handle, Position, NodeProps, NodeResizer } from 'reactflow';
import { PlaceData } from '../types';

export function PlaceNode({ data, selected }: NodeProps<PlaceData>) {
  const isInitial = data.isInitial;
  const hasMetadata = data.meta && Object.keys(data.meta).length > 0;

  const handleMetadataClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    console.log('Metadata button clicked for place:', data.label);
    console.log('Place ID:', data.id);
    console.log('Livewire available?', !!window.Livewire);

    if (data.id) {
      console.log('Dispatching open-place-metadata-modal with placeId:', data.id);
      window.Livewire?.dispatch('open-place-metadata-modal', { placeId: data.id });
    } else {
      console.error('No ID found for place:', data.label);
    }
  };

  return (
    <div className="relative w-full h-full">
      {/* Node Resizer - visible when selected, resize from all sides */}
      <NodeResizer
        isVisible={selected}
        minWidth={96}
        minHeight={96}
        handleStyle={{ width: '12px', height: '12px' }}
        lineStyle={{ borderWidth: '1px' }}
      />

      {/* Place Node - Always Blue */}
      <div
        className={`w-full h-full rounded-md border-4 bg-blue-50 flex items-center justify-center transition-all cursor-pointer px-4 py-4 ${
          selected ? 'border-blue-600 shadow-lg ring-2 ring-blue-300' : 'border-blue-500 shadow-md'
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

        {/* Initial Indicator */}
        {isInitial && (
          <div className="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-md border-2 border-white" />
        )}

        {/* Metadata Button - positioned outside node */}
        <button
          onClick={handleMetadataClick}
          onMouseDown={(e) => e.stopPropagation()}
          className={`absolute -top-4 -right-4 w-7 h-7 rounded-full border-2 border-white shadow-lg flex items-center justify-center transition-all hover:scale-110 cursor-pointer z-10 nodrag nopan ${
            hasMetadata ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 hover:bg-gray-500'
          }`}
          title={hasMetadata ? 'Edit metadata' : 'Add metadata'}
        >
          <svg className="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
        </button>

        {/* Label */}
        <div className="text-sm font-bold text-blue-900 text-center wrap-break-word leading-tight">
          {data.label}
        </div>
      </div>
    </div>
  );
}
