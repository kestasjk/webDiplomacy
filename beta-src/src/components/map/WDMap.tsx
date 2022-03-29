import * as React from "react";
import WDBoardMap from "./variants/classic/components/WDBoardMap";
import CapturableLandTexture from "../../assets/textures/capturable-land.jpeg";
import WaterTexture from "../../assets/textures/sea-texture.png";

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

        <filter id="austria">
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
          <feFlood floodColor="#FC4343" floodOpacity="1" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>

        <filter id="england">
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
          <feFlood floodColor="#E136EA" floodOpacity="1" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>

        <filter id="france">
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
          <feFlood floodColor="#2D5EE8" floodOpacity="1" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>

        <filter id="germany">
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
          <feFlood floodColor="#F37C0E" floodOpacity="1" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>

        <filter id="italy">
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
          <feFlood floodColor="#47D2A0" floodOpacity="1" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>

        <filter id="russia">
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
          <feFlood floodColor="#3F1BC1" floodOpacity="0.85" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>

        <filter id="turkey">
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
          <feFlood floodColor="#F3C400" floodOpacity="1" result="color" />
          <feComposite operator="in" in="color" in2="inverse" result="shadow" />
          <feComponentTransfer in="shadow" result="shadow">
            <feFuncA type="linear" slope="2.5" />
          </feComponentTransfer>
        </filter>
      </defs>
    </svg>
  ),
);

export default React.memo(WDMap);
