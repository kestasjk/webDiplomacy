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
  gameOverview,
  gameStatus,
  gameData,
  gameMaps,
  gameViewedPhase,
  gameLegalOrders,
} from "../../state/game/game-api-slice";
import {
  Unit,
  getUnitsLive,
  getUnitsHistorical,
} from "../../utils/map/getUnits";
import { IOrderData, IOrderDataHistorical } from "../../models/Interfaces";
import provincesMapData from "../../data/map/ProvincesMapData";
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

// TODO big spaghetti to unify webdip's historical representation
// with webdip's live representation
// and with ordersMeta.
// into one single set of data about orders and units that we can
// pass down so that everything else below us renders functionally
// based on that.
const WDMapController: React.FC = function (): React.ReactElement {
  const svgElement = React.useRef<SVGSVGElement>(null);
  const [viewport] = useViewport();
  const dispatch = useAppDispatch();
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const device = getDevice(viewport);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  // FIXME: it's not ideal for us to be fetching the whole world from store here
  // This is hard to untangle though because the representation of the data in the
  // store is relatively bad. You have to depend on a lot of stuff in order to
  // draw useful things right now.
  const viewedPhaseState = useAppSelector(gameViewedPhase);
  const overview = useAppSelector(gameOverview);
  const status = useAppSelector(gameStatus);
  const data = useAppSelector(gameData);
  const maps = useAppSelector(gameMaps);

  const updateForPhase = () => {
    if (
      viewedPhaseState.viewedPhaseIdx >= status.phases.length - 1 &&
      status.status === "Playing"
    ) {
      // Convert from our internal order representation to webdip's
      // historical representation of orders so that we draw
      // our internal orders and webdip's historical orders
      // exactly the same way.

      const ordersHistorical: IOrderDataHistorical[] = [];
      const currentOrdersById: { [key: number]: IOrderData } = {};
      if (data.data.currentOrders) {
        data.data.currentOrders.forEach((orderData) => {
          currentOrdersById[orderData.id] = orderData;
        });
      }
      Object.entries(ordersMeta).forEach(([orderID, orderMeta]) => {
        // FIXME ordersMeta can accumulate garbage over multiple phases.
        // Is there anywhere else where we iterate over it and therefore iterate
        // over garbage orders?
        if (!currentOrdersById[orderID]) {
          return;
        }
        let fromTerrID = 0;
        let toTerrID = 0;
        let terrID = 0;
        let type: string | null = "";
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

          if (type && type.startsWith("Build ")) {
            if (originalOrder.toTerrID) {
              terrID = Number(originalOrder.toTerrID);
            }
            [, unitType] = type.split(" ");
          } else if (originalOrder.unitID) {
            const terrIDString = maps.unitToTerrID[originalOrder.unitID];
            if (terrIDString) {
              terrID = Number(terrIDString);
            }
            unitType = data.data.units[originalOrder.unitID].type;
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

        // !terrID is safe because webdip doesn't seem to use terrID 0.
        if (!type || !unitType || !terrID) {
          return;
        }

        const orderHistorical: IOrderDataHistorical = {
          countryID: status.countryID,
          dislodged: "No",
          fromTerrID,
          phase: overview.phase,
          success: "Yes",
          terrID,
          toTerrID,
          turn: overview.turn,
          type,
          unitType,
          viaConvoy,
        };
        ordersHistorical.push(orderHistorical);
      });
      // console.log("Ordershistorical");
      // console.log(currentOrdersById);
      // console.log(state.game.ordersMeta);
      // console.log(ordersHistorical);

      // Also depends on status, so this is updated both here and when GameStatus is fulfilled.
      const prevPhaseOrders =
        status.phases.length > 1
          ? status.phases[status.phases.length - 2].orders
          : [];
      const units: Unit[] = getUnitsLive(
        data.data.territories,
        data.data.territoryStatuses,
        data.data.units,
        overview.members,
        prevPhaseOrders,
        ordersMeta,
        data.data.currentOrders ? data.data.currentOrders : [],
        overview.user,
        overview.phase,
        maps,
      );

      const centersByProvince: { [key: string]: { ownerCountryID: string } } =
        {};
      data.data.territoryStatuses.forEach((provinceStatus) => {
        const province = maps.terrIDToProvince[provinceStatus.id];
        const ownerCountryID = provinceStatus.ownerCountryID || "0";
        centersByProvince[province] = { ownerCountryID };
      });

      return {
        phase: overview.phase,
        units,
        orders: ordersHistorical,
        centersByProvince,
        isLatestPhase: true,
      };
    }

    const phaseHistorical = status.phases[viewedPhaseState.viewedPhaseIdx];
    const unitsHistorical = phaseHistorical.units;
    const prevPhaseOrders =
      viewedPhaseState.viewedPhaseIdx > 0
        ? status.phases[viewedPhaseState.viewedPhaseIdx - 1].orders
        : [];
    const unitsLive = getUnitsHistorical(
      data.data.territories,
      unitsHistorical,
      overview.members,
      prevPhaseOrders,
      phaseHistorical.orders,
      maps,
    );
    const centersByProvince: {
      [key: string]: { ownerCountryID: string };
    } = {};
    phaseHistorical.centers.forEach((iCenter) => {
      centersByProvince[maps.terrIDToProvince[iCenter.terrID]] = {
        ownerCountryID: iCenter.countryID.toString(),
      };
    });

    return {
      phase: phaseHistorical.phase as string,
      units: unitsLive,
      orders: phaseHistorical.orders,
      centersByProvince,
      isLatestPhase: false,
    };
  };
  const { phase, units, orders, centersByProvince, isLatestPhase } =
    updateForPhase();
  const { territories } = data.data;

  let standoffs: StandoffInfo[] = [];
  if (phase === "Retreats" && viewedPhaseState.viewedPhaseIdx > 0) {
    const provincesWithUnits = new Set(
      units.map((unit) => unit.mappedTerritory.province),
    );
    const prevPhase = status.phases[viewedPhaseState.viewedPhaseIdx - 1];
    const standoffsByProvince: { [key: string]: StandoffInfo } = {};
    prevPhase.orders.forEach((order) => {
      if (order.type === "Move" && order.toTerrID) {
        const province = maps.terrIDToProvince[order.toTerrID];
        if (!provincesWithUnits.has(province)) {
          // If we have a move order to a province but that province has no units now
          // then it's a standoff
          if (!standoffsByProvince[province]) {
            standoffsByProvince[province] = {
              province,
              attemptedMoves: [],
            };
          }
          standoffsByProvince[province].attemptedMoves.push([
            maps.terrIDToTerritory[order.terrID],
            maps.terrIDToTerritory[order.toTerrID],
          ]);
        }
      }
    });
    standoffs = Object.values(standoffsByProvince);
  }

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
