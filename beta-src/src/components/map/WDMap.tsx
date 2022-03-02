import * as React from "react";
import WDBoundaries from "./variants/classic/components/WDBoundaries";
import WDCenters from "./variants/classic/components/WDCenters";
import WDGameBoardOutlines from "./variants/classic/components/WDGameBoardOutlines";
import WDNeutral from "./variants/classic/components/WDNeutral";
import WDSeaAreas from "./variants/classic/components/WDSeaAreas";

const WDMap: React.ForwardRefExoticComponent<
  React.RefAttributes<SVGSVGElement>
> = React.forwardRef(
  (_props, ref): React.ReactElement => (
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
          <g id="bg">
            <path
              id="Vector"
              d="M4017.07 0H0V1991.02H4017.07V0Z"
              fill="#FFF8F3"
            />
          </g>
          <WDCenters />
          <WDGameBoardOutlines />
          <WDNeutral />
          <WDSeaAreas />
          <WDBoundaries />
        </g>
      </g>
      <defs>
        <radialGradient
          id="paint0_radial_807_2305"
          cx="0"
          cy="0"
          r="1"
          gradientUnits="userSpaceOnUse"
          gradientTransform="translate(2461.23 1008.58) scale(1616.06 2343.29)"
        >
          <stop stopColor="#EDDED1" />
          <stop offset="1" stopColor="#D9CDAE" />
        </radialGradient>
        <radialGradient
          id="paint1_radial_807_2305"
          cx="0"
          cy="0"
          r="1"
          gradientUnits="userSpaceOnUse"
          gradientTransform="translate(1828 1751) rotate(90) scale(328 1987.34)"
        >
          <stop stopColor="#EDDED1" />
          <stop offset="1" stopColor="#D9CDAE" />
        </radialGradient>
        <clipPath id="clip0_807_2305">
          <rect width="4017.07" height="2001.58" fill="white" />
        </clipPath>
      </defs>
    </svg>
  ),
);

export default React.memo(WDMap);
