import * as React from "react";
import WDBoardMap from "./variants/classic/components/WDBoardMap";

const WDMap: React.ForwardRefExoticComponent<
  React.RefAttributes<SVGSVGElement>
> = React.forwardRef((_props, ref): React.ReactElement => {
  return (
    <svg
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
    </svg>
  );
});

export default React.memo(WDMap);
