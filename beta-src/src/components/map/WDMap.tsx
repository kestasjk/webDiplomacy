import * as React from "react";
import WDBoardMap from "./variants/classic/components/WDBoardMap";
import CapturableLandTexture from "../../assets/textures/capturable-land.jpeg";
import WaterTexture from "../../assets/textures/sea-texture.png";
import webDiplomacyTheme from "../../webDiplomacyTheme";

const countryFilterConfig = [
  {
    countryName: "austria",
    floodColor: webDiplomacyTheme.palette.Austria.main,
    floodOpacity: 1,
  },
  {
    countryName: "england",
    floodColor: webDiplomacyTheme.palette.England.main,
    floodOpacity: 1,
  },
  {
    countryName: "france",
    floodColor: webDiplomacyTheme.palette.France.main,
    floodOpacity: 1,
  },
  {
    countryName: "germany",
    floodColor: webDiplomacyTheme.palette.Germany.main,
    floodOpacity: 1,
  },
  {
    countryName: "italy",
    floodColor: webDiplomacyTheme.palette.Italy.main,
    floodOpacity: 1,
  },
  {
    countryName: "russia",
    floodColor: webDiplomacyTheme.palette.Russia.main,
    floodOpacity: 0.85,
  },

  {
    countryName: "turkey",
    floodColor: webDiplomacyTheme.palette.Turkey.main,
    floodOpacity: 1,
  },
];

const countryFilterDefs = countryFilterConfig.map((country) => {
  return (
    <filter id={country.countryName} key={country.countryName}>
      <feOffset dx="5" dy="5" in="SourceGraphic" result="shadow1" />
      <feOffset dx="-5" dy="-5" in="SourceGraphic" result="shadow2" />
      <feMerge result="offset-blur">
        <feMergeNode in="shadow1" />
        <feMergeNode in="shadow2" />
        <feMergeNode in="SourceGraphic" />
      </feMerge>
      <feGaussianBlur stdDeviation="7.5" result="offset-blur" />
      <feComposite
        operator="out"
        in="SourceGraphic"
        in2="offset-blur"
        result="inverse"
      />
      <feFlood
        floodColor={country.floodColor}
        floodOpacity={country.floodOpacity}
        result="color"
      />
      <feComposite operator="in" in="color" in2="inverse" result="shadow" />
      <feComponentTransfer in="shadow" result="shadow">
        <feFuncA type="linear" slope="2.5" />
      </feComponentTransfer>
    </filter>
  );
});

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
        {countryFilterDefs}
      </defs>
    </svg>
  ),
);

export default React.memo(WDMap);
