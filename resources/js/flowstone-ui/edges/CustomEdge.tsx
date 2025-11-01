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
              <div className="px-2.5 py-1 rounded-md text-xs font-semibold shadow-sm bg-white text-gray-700 border border-gray-300 cursor-pointer hover:bg-gray-50 transition-colors">
                {label}
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
