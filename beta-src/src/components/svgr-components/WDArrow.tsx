import * as React from "react";

interface WDArrowProps {
  color: string;
}

const WDArrow: React.FC<WDArrowProps> = function ({
  color,
}): React.ReactElement {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 350 100"
      style={{ width: "70px", color: `${color}` }}
    >
      <defs>
        <marker
          id="arrowhead"
          markerWidth="7"
          markerHeight="5"
          refX="0"
          refY="2.5"
          orient="auto"
        >
          <polygon points="0 0, 7 2.5, 0 5" fill={color} />
        </marker>
      </defs>
      <line
        x1="0"
        y1="50"
        x2="250"
        y2="50"
        stroke={color}
        strokeWidth="8"
        markerEnd="url(#arrowhead)"
      />
    </svg>
  );
};

export default WDArrow;
