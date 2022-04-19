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
import getValidUnitBorderCrossings from "../../utils/map/getValidUnitBorderCrossings";
import drawArrow from "../../utils/map/drawArrow";
import ArrowType from "../../enums/ArrowType";
import drawCurrentMoveOrders from "../../utils/map/drawCurrentMoveOrders";
import processNextCommand from "../../utils/processNextCommand";
import getTerritoriesMeta from "../../utils/getTerritoriesMeta";
import getUnits from "../../utils/map/getUnits";
import { GameCommand } from "../../state/interfaces/GameCommands";
import UnitType from "../../types/UnitType";
import Territory from "../../enums/map/variants/classic/Territory";

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

  const commandActions = {
    DRAW_ARROW: (command) => {
      const [key, value] = command;
      const { orderID, arrow } = value.data;
      drawArrow(`${orderID}`, ArrowType.MOVE, arrow.to, arrow.from);
      deleteCommand(key);
    },
    REMOVE_ARROW: (command) => {
      const [key, value] = command;
      d3.selectAll(`.arrow__${value.data.orderID}`).remove();
      deleteCommand(key);
    },
    INVALID_CLICK: (command) => {
      const [key, value] = command;
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
    },
  };

  processNextCommand(commands, commandActions);

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
      const unitsToDraw = getUnits(data, members);
      unitsToDraw.forEach(({ country, mappedTerritory, unit }) => {
        console.log({
          country,
        });
        const command: GameCommand = {
          command: "SET_UNIT",
          data: {
            setUnit: {
              componentType: "Game",
              country,
              mappedTerritory,
              unit,
              unitType: unit.type as UnitType,
              unitSlotName: mappedTerritory.unitSlotName,
            },
          },
        };
        dispatch(
          gameApiSliceActions.dispatchCommand({
            command,
            container: "territoryCommands",
            identifier: Territory[mappedTerritory.territory],
          }),
        );
      });
      const ordersMetaUpdates = getValidUnitBorderCrossings(data);
      dispatch(gameApiSliceActions.updateOrdersMeta(ordersMetaUpdates));
      setTimeout(() => {
        drawCurrentMoveOrders(data);
      });
    }
  }, [data, members]);

  React.useEffect(() => {
    if (data) {
      dispatch(
        gameApiSliceActions.updateTerritoriesMeta(getTerritoriesMeta(data)),
      );
      dispatch(gameApiSliceActions.highlightMapTerritories());
    }
  }, [data]);

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
