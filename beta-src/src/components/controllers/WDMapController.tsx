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
import drawArrow from "../../utils/map/drawArrow";
import ArrowType from "../../enums/ArrowType";
import ArrowColor from "../../enums/ArrowColor";
import getUnits from "../../utils/map/getUnits";
import {
  IOrderData,
  IOrderDataHistorical,
  IUnit,
} from "../../models/Interfaces";

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
  const ordersMeta = useAppSelector(gameOrdersMeta);

  const device = getDevice(viewport);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  const arrows = useAppSelector((state) => state.game.arrows);
  const [phase, units, orders, territories] = useAppSelector((state) => {
    if (
      state.game.viewedPhaseState.viewedPhaseIdx >=
      state.game.status.phases.length - 1
    ) {
      // Convert from our internal order representation to webdip's
      // historical representation of orders so that we draw
      // our internal orders and webdip's historical orders
      // exactly the same way.

      const ordersHistorical: IOrderDataHistorical[] = [];
      const currentOrdersById: { [key: number]: IOrderData } = {};
      if (state.game.data.data.currentOrders) {
        state.game.data.data.currentOrders.forEach((orderData) => {
          currentOrdersById[orderData.id] = orderData;
        });
      }
      Object.entries(state.game.ordersMeta).forEach(([orderID, orderMeta]) => {
        let fromTerrID = 0;
        let toTerrID = 0;
        let terrID = 0;
        let type = "";
        let unitType = "";
        let viaConvoy;

        let { originalOrder } = orderMeta;
        if (!originalOrder) {
          originalOrder = currentOrdersById[orderID];
        }
        if (originalOrder) {
          if (originalOrder.fromTerrID) {
            fromTerrID = Number(originalOrder.fromTerrID);
          }
          if (originalOrder.toTerrID) {
            toTerrID = Number(originalOrder.toTerrID);
          }
          type = originalOrder.type;

          if (type.startsWith("Build ")) {
            if (originalOrder.toTerrID) {
              terrID = Number(originalOrder.toTerrID);
            }
            [, unitType] = type.split(" ");
          } else {
            const terrIDString =
              state.game.maps.unitToTerritory[originalOrder.unitID];
            if (terrIDString) {
              terrID = Number(terrIDString);
            }
            unitType = state.game.data.data.units[originalOrder.unitID].type;
          }

          if (originalOrder.viaConvoy === "Yes") {
            viaConvoy = "Yes";
          } else {
            viaConvoy = "No";
          }
        }
        if (orderMeta.update) {
          if (orderMeta.update.fromTerrID !== undefined) {
            fromTerrID = Number(orderMeta.update.fromTerrID);
          }
          toTerrID = Number(orderMeta.update.toTerrID);
          type = orderMeta.update.type;
          if (orderMeta.update.viaConvoy === "Yes") {
            viaConvoy = "Yes";
          } else {
            viaConvoy = "No";
          }
        }
        const orderHistorical: IOrderDataHistorical = {
          countryID: state.game.status.countryID.toString(),
          dislodged: "No",
          fromTerrID,
          phase: state.game.overview.phase,
          success: "Yes",
          terrID,
          toTerrID,
          turn: state.game.overview.turn,
          type,
          unitType,
          viaConvoy,
        };
        ordersHistorical.push(orderHistorical);
      });
      console.log("Ordershistorical");
      console.log(currentOrdersById);
      console.log(state.game.ordersMeta);
      console.log(ordersHistorical);

      return [
        state.game.overview.phase,
        state.game.units,
        ordersHistorical,
        state.game.data.data.territories,
      ];
    }

    // TODO this is a bit horrible. Let's move this somewhere or put some API
    // on getting data from the status and/or data and move this there?
    // Convert from webdip's historical representation to webdip's
    // live representation of units
    const phaseHistorical =
      state.game.status.phases[state.game.viewedPhaseState.viewedPhaseIdx];
    const unitsHistorical = phaseHistorical.units;
    const unitsConverted: { [key: string]: IUnit } = {};
    unitsHistorical.forEach((unitHistorical, index) => {
      unitsConverted[index] = {
        id: index.toString(),
        countryID: unitHistorical.countryID.toString(),
        type: unitHistorical.unitType,
        terrID: unitHistorical.terrID.toString(),
      };
    });
    const unitsLive = getUnits(
      state.game.data.data.territories,
      unitsConverted,
      state.game.overview.members,
    );
    return [
      phaseHistorical.phase,
      unitsLive,
      phaseHistorical.orders,
      state.game.data.data.territories,
    ];
  });
  const maps = useAppSelector((state) => state.game.maps);

  // ideally the arrows would be rendered as FCs declaratively,
  // rather than imperatively through the drawArrow function,
  // but that's too much work.
  arrows.forEach((arrow, arrowIdx) => {
    drawArrow(
      String(arrowIdx),
      ArrowType.MOVE,
      ArrowColor.MOVE,
      "territory",
      arrow.to,
      arrow.from,
    );
  });

  React.useLayoutEffect(() => {
    if (svgElement.current) {
      const fullMap = d3.select(svgElement.current);
      const contained = fullMap.select("#container");
      const containedRect = contained.node().getBBox();
      const gameBoardAreaRect = fullMap
        .select("#playableTerritories")
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
  console.log("Renderd MapController");
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
      />
    </div>
  );
};

export default WDMapController;
