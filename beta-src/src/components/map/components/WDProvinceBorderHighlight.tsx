import * as React from "react";
import { ProvinceMapData } from "../../../interfaces";

interface WDProvinceBorderHighlightProps {
  provinceMapData: ProvinceMapData;
}

const WDProvinceBorderHighlight: React.FC<WDProvinceBorderHighlightProps> =
  function ({ provinceMapData }): React.ReactElement {
    const { province } = provinceMapData;

    return (
      <svg
        height={provinceMapData.height}
        id={`${province}-province-overlay`}
        viewBox={provinceMapData.viewBox}
        width={provinceMapData.width}
        x={provinceMapData.x}
        y={provinceMapData.y}
        overflow="visible"
      >
        <path
          d={provinceMapData.path}
          fill="none"
          fillOpacity={0.0}
          id={`${province}-choice-outline`}
          stroke="black"
          strokeOpacity={1}
          strokeWidth={5}
        />
      </svg>
    );
  };

export default WDProvinceBorderHighlight;
