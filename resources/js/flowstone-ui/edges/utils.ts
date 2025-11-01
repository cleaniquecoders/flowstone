import { Position, XYPosition, Node } from 'reactflow';

// Get node dimensions with fallback
function getNodeDimensions(node: any): { width: number; height: number } {
  // Try different property paths for dimensions
  const width = node.width || node.measured?.width || node.style?.width || 100;
  const height = node.height || node.measured?.height || node.style?.height || 100;

  return {
    width: typeof width === 'number' ? width : 100,
    height: typeof height === 'number' ? height : 100,
  };
}

// Get absolute position with fallback
function getNodePosition(node: any): XYPosition {
  // Try different property paths for position
  const positionAbsolute = node.positionAbsolute || node.position || { x: 0, y: 0 };

  return {
    x: positionAbsolute.x || 0,
    y: positionAbsolute.y || 0,
  };
}

// this helper function returns the intersection point
// of the line between the center of the intersectionNode and the target node
function getNodeIntersection(intersectionNode: any, targetNode: any): XYPosition {
  const intersectionDimensions = getNodeDimensions(intersectionNode);
  const intersectionPosition = getNodePosition(intersectionNode);
  const targetPosition = getNodePosition(targetNode);

  const w = intersectionDimensions.width / 2;
  const h = intersectionDimensions.height / 2;

  const x2 = intersectionPosition.x + w;
  const y2 = intersectionPosition.y + h;
  const x1 = targetPosition.x + w;
  const y1 = targetPosition.y + h;

  const xx1 = (x1 - x2) / (2 * w) - (y1 - y2) / (2 * h);
  const yy1 = (x1 - x2) / (2 * w) + (y1 - y2) / (2 * h);
  const a = 1 / (Math.abs(xx1) + Math.abs(yy1));
  const xx3 = a * xx1;
  const yy3 = a * yy1;
  const x = w * (xx3 + yy3) + x2;
  const y = h * (-xx3 + yy3) + y2;

  return { x, y };
}

// returns the position (top,right,bottom or left) passed node compared to the intersection point
function getEdgePosition(node: any, intersectionPoint: XYPosition): Position {
  const position = getNodePosition(node);
  const dimensions = getNodeDimensions(node);

  const nx = Math.round(position.x);
  const ny = Math.round(position.y);
  const px = Math.round(intersectionPoint.x);
  const py = Math.round(intersectionPoint.y);

  if (px <= nx + 1) {
    return Position.Left;
  }
  if (px >= nx + dimensions.width - 1) {
    return Position.Right;
  }
  if (py <= ny + 1) {
    return Position.Top;
  }
  if (py >= ny + dimensions.height - 1) {
    return Position.Bottom;
  }

  return Position.Top;
}

// returns the parameters (sx, sy, tx, ty, sourcePos, targetPos) you need to create an edge
export function getEdgeParams(source: any, target: any) {
  const sourceIntersectionPoint = getNodeIntersection(source, target);
  const targetIntersectionPoint = getNodeIntersection(target, source);

  const sourcePos = getEdgePosition(source, sourceIntersectionPoint);
  const targetPos = getEdgePosition(target, targetIntersectionPoint);

  return {
    sx: sourceIntersectionPoint.x,
    sy: sourceIntersectionPoint.y,
    tx: targetIntersectionPoint.x,
    ty: targetIntersectionPoint.y,
    sourcePos,
    targetPos,
  };
}
