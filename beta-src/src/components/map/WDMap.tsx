import * as React from "react";
import WDBoardMap from "./variants/classic/components/WDBoardMap";
import CapturableLandTexture from "../../assets/textures/capturable-land.jpeg";
import WaterTexture from "../../assets/textures/sea-texture.png";
import WDArrowMarkerDefs from "../../utils/map/WDArrowMarkerDefs";
import WDBuildContainer from "./components/WDBuildContainer";
import WDFlyoutContainer from "./components/WDFlyoutContainer";
import WDArrowContainer, { StandoffInfo } from "./components/WDArrowContainer";
import { Unit } from "../../utils/map/getUnits";
import { IOrderDataHistorical } from "../../models/Interfaces";
import GameStateMaps from "../../state/interfaces/GameStateMaps";
import { APITerritories } from "../../state/interfaces/GameDataResponse";
import Territory from "../../enums/map/variants/classic/Territory";
import Province from "../../enums/map/variants/classic/Province";

interface WDMapProps {
  units: Unit[];
  phase: string;
  orders: IOrderDataHistorical[];
  maps: GameStateMaps;
  territories: APITerritories;
  centersByProvince: { [key: string]: { ownerCountryID: string } };
  standoffs: StandoffInfo[];
  isLivePhase: boolean; // Game is live and user is viewing the latest phase?
}

const WDMap: React.ForwardRefExoticComponent<
  WDMapProps & React.RefAttributes<SVGSVGElement>
> = React.forwardRef<SVGSVGElement, WDMapProps>(
  (
    {
      units,
      phase,
      orders,
      maps,
      territories,
      centersByProvince,
      standoffs,
      isLivePhase,
    },
    ref,
  ): React.ReactElement => (
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
          <WDBoardMap
            units={units}
            centersByProvince={centersByProvince}
            phase={phase}
            isLivePhase={isLivePhase}
          />
          <WDArrowContainer
            orders={orders}
            units={units}
            maps={maps}
            territories={territories}
            standoffs={standoffs}
          />
          {isLivePhase && <WDBuildContainer />}
          {isLivePhase && <WDFlyoutContainer units={units} />}
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
        {WDArrowMarkerDefs()}

        <pattern
          id="neutral-land-texture"
          width="30"
          height="10"
          patternTransform="rotate(45 0 0)"
          patternUnits="userSpaceOnUse"
        >
          <rect
            x="0"
            y="0"
            width="30"
            height="10"
            style={{ fill: "#C5BFBE" }}
          />
          <line
            x1="0"
            y1="0"
            x2="0"
            y2="10"
            style={{ stroke: "#B5AFAE", strokeWidth: 30 }}
          />
        </pattern>

        <filter id="selectionGlow" height="120%" width="120%" x="-10%" y="-10%">
          <feMorphology
            operator="dilate"
            radius="5"
            in="SourceAlpha"
            result="thickerSource"
          />
          <feGaussianBlur
            stdDeviation="8"
            in="thickerSource"
            result="blurredSource"
          />
          <feFlood floodColor="rgb(100,200,255)" result="glowColor" />
          <feComposite
            in="glowColor"
            in2="blurredSource"
            operator="in"
            result="selectionGlowGlow"
          />
        </filter>
        <filter id="choiceGlow" height="120%" width="120%" x="-10%" y="-10%">
          <feMorphology
            operator="dilate"
            radius="1"
            in="SourceAlpha"
            result="thickerSource"
          />
          <feGaussianBlur
            stdDeviation="6"
            in="thickerSource"
            result="blurredSource"
          />
          <feFlood floodColor="rgb(255,255,255)" result="glowColor" />
          <feComposite
            in="glowColor"
            in2="blurredSource"
            operator="in"
            result="choicesGlowGlow"
          />
        </filter>
      </defs>
    </svg>
  ),
);

export default React.memo(WDMap);
