import * as React from "react";
import * as d3 from "d3";
import Device from "../../enums/Device";
import getInitialViewTranslation from "../../utils/map/getInitialViewTranslation";
import Scale from "../../types/Scale";
import WDMap from "./WDMap";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameApiSliceActions,
  gameData,
  gameOverview,
} from "../../state/game/game-api-slice";
import drawUnitsOnMap from "../../utils/map/drawUnitsOnMap";

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

const WDMapController: React.FC = function (): React.ReactElement {
  const svgElement = React.useRef<SVGSVGElement>(null);
  const [viewport] = useViewport();
  const dispatch = useAppDispatch();
  const { data } = useAppSelector(gameData);
  const { members } = useAppSelector(gameOverview);
  if ("currentOrders" in data && data.currentOrders) {
    dispatch(
      gameApiSliceActions.markOrdersAsSaved(
        data.currentOrders.map((order) => order.id),
      ),
    );
  }
  const device = getDevice(viewport);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  React.useLayoutEffect(() => {
    if (svgElement.current) {
      const fullMap = d3.select(svgElement.current);
      const contained = fullMap.select("#container");
      const containedRect = contained.node().getBBox();
      const gameBoardAreaRect = fullMap.select("#outlines").node().getBBox();

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
        .on("zoom", zoom);

      fullMap
        .on("wheel", (e) => e.preventDefault())
        .call(d3Zoom)
        .call(d3Zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale))
        .on("dblclick.zoom", null);
    }
  }, [svgElement, viewport]);

  React.useLayoutEffect(() => {
    if (data && members) {
      drawUnitsOnMap(members, data);
      dispatch(gameApiSliceActions.highlightMapTerritories());
    }
  }, [svgElement, data]);

  return (
    <div
      style={{
        width: viewport.width,
        height: viewport.height,
      }}
    >
      <WDMap ref={svgElement} />
    </div>
  );
};

export default WDMapController;
