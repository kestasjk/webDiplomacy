import * as React from "react";
import * as d3 from "d3";
import Device from "../../enums/Device";
import getInitialViewTranslation from "../../utils/map/getInitialViewTranslation";
import Scale from "../../types/Scale";
import WDMap from "../map/WDMap";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameApiSliceActions,
  gameOrdersMeta,
} from "../../state/game/game-api-slice";
import { Unit } from "../../utils/map/getUnits";
import { IOrderDataHistorical } from "../../models/Interfaces";
import GameStateMaps from "../../state/interfaces/GameStateMaps";
import { APITerritories } from "../../state/interfaces/GameDataResponse";
import Province from "../../enums/map/variants/classic/Province";
import { StandoffInfo } from "../map/components/WDArrowContainer";

const Scales: Scale = {
  DESKTOP: [0.45, 3],
  MOBILE_LG: [0.32, 1.6],
  MOBILE_LG_LANDSCAPE: [0.3, 1.6],
  MOBILE: [0.32, 1.6],
  MOBILE_LANDSCAPE: [0.27, 1.6],
  TABLET: [0.6275, 3],
  TABLET_LANDSCAPE: [0.6, 3],
};

const getInitialScaleForDevice = (device: Device): number[] => {
  return Scales[device];
};

const mapOriginalWidth = 6010;
const mapOriginalHeight = 3005;

interface WDMapControllerProps {
  units: Unit[];
  phase: string;
  orders: IOrderDataHistorical[];
  maps: GameStateMaps;
  territories: APITerritories;
  centersByProvince: { [key: string]: { ownerCountryID: string } };
  standoffs: StandoffInfo[];
  isLatestPhase: boolean;
}

const WDMapController: React.FC<WDMapControllerProps> = function ({
  units,
  phase,
  orders,
  maps,
  territories,
  centersByProvince,
  isLatestPhase,
  standoffs,
}): React.ReactElement {
  const svgElement = React.useRef<SVGSVGElement>(null);
  const [viewport] = useViewport();
  const dispatch = useAppDispatch();
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const device = getDevice(viewport);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  // const legalOrders = useAppSelector(gameLegalOrders);
  // console.log({ legalOrders });

  React.useLayoutEffect(() => {
    if (svgElement.current) {
      const fullMap = d3.select(svgElement.current);
      const contained = fullMap.select("#container");
      const containedRect = contained.node().getBBox();
      const gameBoardAreaRect = fullMap
        .select("#playableProvinces")
        .node()
        .getBBox();

      const { scale, x, y } = getInitialViewTranslation(
        containedRect,
        gameBoardAreaRect,
        scaleMin,
        viewport,
      );

      const zoom = ({ transform }) => {
        contained.attr("transform", transform);
      };

      const d3Zoom = d3
        .zoom()
        .translateExtent([
          [0, 0],
          [mapOriginalWidth, mapOriginalHeight],
        ])
        .scaleExtent([scale, scaleMax])
        .clickDistance(3)
        .on("zoom", zoom);

      fullMap
        .on("wheel", (e) => e.preventDefault())
        .call(d3Zoom)
        .call(d3Zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale))
        .on("dblclick.zoom", null);
    }
  }, [svgElement, viewport]);

  React.useEffect(() => {
    setTimeout(() => {
      dispatch(gameApiSliceActions.updateOrdersMeta(ordersMeta));
    }, 500);
  }, []);

  React.useEffect(() => {
    const keydownHandler = (e) => {
      const keyCode = e.which || e.keyCode;
      const ESCAPE = 27;
      // console.log("KEYCODE");
      // console.log(keyCode);
      if (keyCode === ESCAPE) {
        e.preventDefault();
        // console.log("DISPATCH RESET ORDER");
        dispatch(gameApiSliceActions.resetOrder());
      }
    };
    // console.log("ADDING HANLDER");
    window.addEventListener("keydown", keydownHandler);
    return () => window.removeEventListener("keydown", keydownHandler);
  });

  return (
    <div
      style={{
        width: viewport.width,
        height: viewport.height,
      }}
    >
      <WDMap
        ref={svgElement}
        units={units}
        phase={phase}
        orders={orders}
        maps={maps}
        territories={territories}
        centersByProvince={centersByProvince}
        standoffs={standoffs}
        isLatestPhase={isLatestPhase}
      />
    </div>
  );
};

export default WDMapController;
