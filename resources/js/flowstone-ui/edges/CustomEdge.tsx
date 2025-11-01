import React from 'react';
import { BaseEdge, EdgeLabelRenderer, EdgeProps, getSmoothStepPath, useStore } from 'reactflow';
import { getEdgeParams } from './utils';

/**
 * CustomEdge - Floating smooth step edge with optional label
 * Edges stick to the top, right, bottom or left side of the nodes
 */
export function CustomEdge({
  id,
  source,
  target,
  style = {},
  markerEnd,
  label,
  data,
}: EdgeProps) {
  const { sourceNode, targetNode } = useStore((s) => {
    const sourceNode = s.nodeInternals.get(source);
    const targetNode = s.nodeInternals.get(target);

    return { sourceNode, targetNode };
  });

  if (!sourceNode || !targetNode) {
    console.warn('CustomEdge: Source or target node not found', { source, target });
    return null;
  }

  try {
    const { sx, sy, tx, ty, sourcePos, targetPos } = getEdgeParams(sourceNode, targetNode);

    const [edgePath, labelX, labelY] = getSmoothStepPath({
      sourceX: sx,
      sourceY: sy,
      sourcePosition: sourcePos,
      targetX: tx,
      targetY: ty,
      targetPosition: targetPos,
    });

    return (
      <>
        <BaseEdge
          id={id}
          path={edgePath}
          markerEnd={markerEnd}
          style={style}
          interactionWidth={20}
        />
        {label && (
          <EdgeLabelRenderer>
            <div
              className="edge-label-positioned nodrag nopan"
              style={{
                position: 'absolute',
                transform: 'translate(-50%, -50%)',
                left: `${labelX}px`,
                top: `${labelY}px`,
                pointerEvents: 'all',
              }}
            >
              <div className="flex items-center gap-1.5">
                <div className="px-2.5 py-1 rounded-md text-xs font-semibold shadow-sm bg-white text-gray-700 border border-gray-300 cursor-pointer hover:bg-gray-50 transition-colors">
                  {label}
                </div>
                {data?.transitionId && (
                  <button
                    onClick={(e) => {
                      e.stopPropagation();
                      console.log('Edge metadata button clicked for transition:', data.transitionKey);
                      console.log('Transition ID:', data.transitionId);
                      console.log('Livewire available?', !!(window as any).Livewire);

                      if (data.transitionId) {
                        console.log('Dispatching open-transition-metadata-modal with transitionId:', data.transitionId);
                        (window as any).Livewire?.dispatch('open-transition-metadata-modal', { transitionId: data.transitionId });
                      }
                    }}
                    onMouseDown={(e) => e.stopPropagation()}
                    className={`w-6 h-6 rounded-full border-2 border-white shadow-lg flex items-center justify-center transition-all hover:scale-110 cursor-pointer nodrag nopan ${
                      data?.metadata && Object.keys(data.metadata).length > 0
                        ? 'bg-orange-600 hover:bg-orange-700'
                        : 'bg-gray-400 hover:bg-gray-500'
                    }`}
                    title={data?.metadata && Object.keys(data.metadata).length > 0 ? 'Edit metadata' : 'Add metadata'}
                  >
                    <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                )}
              </div>
            </div>
          </EdgeLabelRenderer>
        )}
      </>
    );
  } catch (error) {
    console.error('CustomEdge error:', error, { sourceNode, targetNode });
    return null;
  }
}
