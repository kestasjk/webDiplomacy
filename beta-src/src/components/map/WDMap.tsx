import * as React from "react";
import WDBoardMap from "./variants/classic/components/WDBoardMap";
import CapturableLandTexture from "../../assets/textures/capturable-land.jpeg";
import WaterTexture from "../../assets/textures/sea-texture.png";
import WDCountryHighlightFilterDefs from "../../utils/map/WDCountryHighlightFilters";
import WDArrowMarkerDefs from "../../utils/map/WDArrowMarkerDefs";

const WDMap: React.ForwardRefExoticComponent<
  React.RefAttributes<SVGSVGElement>
> = React.forwardRef(
  (_props, ref): React.ReactElement => (
    <svg
      id="map"
      fill="none"
      ref={ref}
      style={{
        width: "100%",
        height: "100%",
      }}
      xmlns="http://www.w3.org/2000/svg"
    >
      <g id="full-map-svg">
        <g id="container">
          <WDBoardMap />
        </g>
      </g>
      <defs>
        <pattern
          id="capturable-land"
          patternUnits="userSpaceOnUse"
          width="1546"
          height="1384"
        >
          <image
            href={CapturableLandTexture}
            x="0"
            y="0"
            width="1546"
            height="1384"
          />
        </pattern>
        <pattern
          id="sea-texture"
          patternUnits="userSpaceOnUse"
          width="1546"
          height="1384"
        >
          <image href={WaterTexture} x="0" y="0" width="1966" height="1615" />
        </pattern>
        {WDCountryHighlightFilterDefs()}
        {WDArrowMarkerDefs()}
      </defs>
    </svg>
  ),
);

export default React.memo(WDMap);
