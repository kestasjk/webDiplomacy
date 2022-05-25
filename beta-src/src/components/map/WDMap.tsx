import * as React from "react";
import WDBoardMap from "./variants/classic/components/WDBoardMap";
import CapturableLandTexture from "../../assets/textures/capturable-land.jpeg";
import WaterTexture from "../../assets/textures/sea-texture.png";
import WDArrowMarkerDefs from "../../utils/map/WDArrowMarkerDefs";
import WDBuildContainer from "./components/WDBuildContainer";
import WDFlyoutContainer from "./components/WDFlyoutContainer";
import WDArrowContainer from "./components/WDArrowContainer";
import { Unit } from "../../utils/map/getUnits";
import { IOrderDataHistorical } from "../../models/Interfaces";
import GameStateMaps from "../../state/interfaces/GameStateMaps";
import { APITerritories } from "../../state/interfaces/GameDataResponse";
import Territory from "../../enums/map/variants/classic/Territory";
import Territories from "../../data/Territories";

interface WDMapProps {
  units: Unit[];
  phase: string;
  orders: IOrderDataHistorical[];
  maps: GameStateMaps;
  territories: APITerritories;
}

const WDMap: React.ForwardRefExoticComponent<
  WDMapProps & React.RefAttributes<SVGSVGElement>
> = React.forwardRef<SVGSVGElement, WDMapProps>(
  ({ units, phase, orders, maps, territories }, ref): React.ReactElement => (
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
          <WDBoardMap units={units} />
          <WDArrowContainer
            phase={phase}
            orders={orders}
            units={units}
            maps={maps}
            territories={territories}
          />
          <WDBuildContainer />
          <WDFlyoutContainer />
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
      </defs>
    </svg>
  ),
);

export default React.memo(WDMap);
