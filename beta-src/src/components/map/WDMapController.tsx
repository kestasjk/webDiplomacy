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
import getValidUnitBorderCrossings from "../../utils/map/getValidUnitBorderCrossings";
import drawArrow from "../../utils/map/drawArrow";
import ArrowType from "../../enums/ArrowType";
import drawCurrentMoveOrders from "../../utils/map/drawCurrentMoveOrders";

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
  const commands = useAppSelector(
    (state) => state.game.commands.mapCommands.all,
  );

  const device = getDevice(viewport);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  const deleteCommand = (key) => {
    dispatch(
      gameApiSliceActions.deleteCommand({
        type: "mapCommands",
        id: "all",
        command: key,
      }),
    );
  };

  if (commands && commands.size > 0) {
    const firstCommand = commands.entries().next().value;
    if (firstCommand) {
      const [key, value] = firstCommand;
      switch (value.command) {
        case "DRAW_ARROW": {
          const { orderID, arrow } = value.data;
          const { from, to } = arrow;
          const arrowIdentifier = `${orderID}`;
          drawArrow(arrowIdentifier, ArrowType.MOVE, to, from);
          deleteCommand(key);
          break;
        }
        case "REMOVE_ARROW": {
          const { orderID } = value.data;
          d3.selectAll(`.arrow__${orderID}`).remove();
          deleteCommand(key);
          break;
        }
        case "INVALID_CLICK": {
          const { evt, territoryName } = value.data.click;
          const territorySelection = d3.select(`#${territoryName}-territory`);
          const territory: SVGSVGElement = territorySelection.node();
          if (territory) {
            const screenCTM = territory.getScreenCTM();
            if (screenCTM) {
              const pt = territory.createSVGPoint();
              pt.x = evt.clientX;
              pt.y = evt.clientY;
              const { x, y } = pt.matrixTransform(screenCTM.inverse());
              territorySelection
                .append("circle")
                .attr("cx", x)
                .attr("cy", y)
                .attr("r", 6.5)
                .attr("fill", "red")
                .attr("fill-opacity", 0.4)
                .attr("class", "invalid-click");
              territorySelection
                .append("circle")
                .attr("cx", x)
                .attr("cy", y)
                .attr("r", 14)
                .attr("fill", "red")
                .attr("fill-opacity", 0.2)
                .attr("class", "invalid-click");
              setTimeout(() => {
                d3.selectAll(".invalid-click").remove();
              }, 100);
            }
          }
          deleteCommand(key);
          break;
        }
        default:
          break;
      }
    }
  }

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

      if ("currentOrders" in data && data.currentOrders) {
        const ordersMetaUpdates = {};
        Object.values(data.currentOrders).forEach((order) => {
          ordersMetaUpdates[order.id] = {
            saved: true,
          };
        });
        dispatch(gameApiSliceActions.updateOrdersMeta(ordersMetaUpdates));
      }
    }
  }, [svgElement, viewport]);

  React.useLayoutEffect(() => {
    if (data && members) {
      drawUnitsOnMap(members, data);
      dispatch(gameApiSliceActions.highlightMapTerritories());
      const ordersMetaUpdates = getValidUnitBorderCrossings(data);
      dispatch(gameApiSliceActions.updateOrdersMeta(ordersMetaUpdates));
      setTimeout(() => {
        drawCurrentMoveOrders(data);
      });
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
