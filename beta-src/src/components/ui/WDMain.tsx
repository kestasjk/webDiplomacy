import * as React from "react";
import WDMainController from "../controllers/WDMainController";
import WDUI from "./WDUI";
import {
  gameOrdersMeta,
  gameOverview,
  gameStatus,
  gameData,
  gameMaps,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import { IOrderData, IOrderDataHistorical } from "../../models/Interfaces";
import {
  getUnitsHistorical,
  getUnitsLive,
  Unit,
} from "../../utils/map/getUnits";
import { StandoffInfo } from "../map/components/WDArrowContainer";
import WDGameFinishedOverlay from "./WDGameFinishedOverlay";

const WDMapController = React.lazy(
  () => import("../controllers/WDMapController"),
);

const WDMain: React.FC = function (): React.ReactElement {
  // console.log("WDMain rerendered");

  // FIXME: it's not ideal for us to be fetching the whole world from store here
  // This is hard to untangle though because the representation of the data in the
  // store is relatively bad. You have to depend on a lot of stuff in order to
  // draw useful things right now.
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const viewedPhaseState = useAppSelector(gameViewedPhase);
  const overview = useAppSelector(gameOverview);
  const status = useAppSelector(gameStatus);
  const data = useAppSelector(gameData);
  const maps = useAppSelector(gameMaps);

  // Webdip API sometimes gives us entirely bogus or erroneous data on the last phase
  // of a finished game in the historical phase list. So instead, just get the units
  // and orders of the phase before the last phase.
  // drawingPhaseIdx is the phase that we actually do our drawing based on.
  let drawingPhaseIdx = viewedPhaseState.viewedPhaseIdx;
  if (
    overview.phase === "Finished" &&
    viewedPhaseState.viewedPhaseIdx > 0 &&
    viewedPhaseState.viewedPhaseIdx === status.phases.length - 1
  ) {
    drawingPhaseIdx = viewedPhaseState.viewedPhaseIdx - 1;
  }

  const updateForPhase = () => {
    // Only do live viewing if game is not over and not spectating
    const isPlayingGame =
      overview.phase !== "Finished" &&
      status.status === "Playing" &&
      !!overview.user;
    if (
      viewedPhaseState.viewedPhaseIdx >= status.phases.length - 1 &&
      isPlayingGame &&
      overview.user
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
        let unitID = "";
        let fromTerrID = 0;
        let toTerrID = 0;
        let type: string | null = "";
        let viaConvoy;

        let { originalOrder } = orderMeta;
        if (!originalOrder) {
          originalOrder = currentOrdersById[orderID];
        }
        if (originalOrder) {
          unitID = originalOrder.unitID;
          if (originalOrder.fromTerrID) {
            fromTerrID = Number(originalOrder.fromTerrID);
          }
          if (originalOrder.toTerrID) {
            toTerrID = Number(originalOrder.toTerrID);
          }
          type = originalOrder.type;
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

        let terrID = 0;
        let unitType = "";

        if (type && type.startsWith("Build ")) {
          if (toTerrID) {
            terrID = toTerrID;
          }
          [, unitType] = type.split(" ");
        } else if (type && type.startsWith("Destroy")) {
          if (toTerrID) {
            terrID = toTerrID;
          }
          if (terrID) {
            const unitsInProv =
              maps.provinceToUnits[maps.terrIDToProvince[terrID.toString()]];
            if (unitsInProv && unitsInProv[0]) {
              unitType = data.data.units[unitsInProv[0]].type || "";
            }
          }
          // console.log({ terrID, unitType });
        } else if (unitID) {
          const terrIDString = maps.unitToTerrID[unitID];
          if (terrIDString) {
            terrID = Number(terrIDString);
          }
          unitType = data.data.units[unitID].type;
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
        drawingPhase: overview.phase,
        units,
        orders: ordersHistorical,
        centersByProvince,
        isLivePhase: true,
      };
    }

    if (!status.phases[drawingPhaseIdx]) {
      return {
        phase: "",
        drawingPhase: "",
        units: [],
        orders: [],
        centersByProvince: {},
        isLivePhase: isPlayingGame,
      };
    }

    const phaseHistorical = status.phases[drawingPhaseIdx];
    const unitsHistorical = phaseHistorical.units;
    const prevPhaseOrders =
      drawingPhaseIdx > 0 ? status.phases[drawingPhaseIdx - 1].orders : [];
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

    const drawingPhase = phaseHistorical.phase as string;
    // The actual phase label corresponding to the viewed phase,
    // rather than the drawingPhaseIdx's phase.
    const phase =
      viewedPhaseState.viewedPhaseIdx === status.phases.length - 1
        ? overview.phase
        : drawingPhase;

    return {
      phase,
      drawingPhase,
      units: unitsLive,
      orders: phaseHistorical.orders,
      centersByProvince,
      isLivePhase: false,
    };
  };
  const { phase, drawingPhase, units, orders, centersByProvince, isLivePhase } =
    updateForPhase();
  const { territories } = data.data;

  let standoffs: StandoffInfo[] = [];
  if (drawingPhase === "Retreats" && drawingPhaseIdx > 0) {
    const provincesWithUnits = new Set(
      units.map((unit) => unit.mappedTerritory.province),
    );
    const prevPhase = status.phases[drawingPhaseIdx - 1];
    const standoffsByProvince: { [key: string]: StandoffInfo } = {};
    prevPhase.orders.forEach((order) => {
      if (order.type === "Move" && order.toTerrID) {
        const provID = maps.terrIDToProvinceID[order.toTerrID];
        const province = maps.terrIDToProvince[order.toTerrID];
        if (!provincesWithUnits.has(province)) {
          // FIXME: This logic is wrong because a unit might fail to move
          // due to it being a convoy and the convoying fleet being dislodged!
          // Rarely, this may cause us to graphically indicate a standoff on historical
          // phases, when actually there was none.
          // Probably the way to fix this is to get the API to give us the historical
          // standoff status of different provinces - right now, it does NOT do so.
          //
          // If we have a move order to a province but that province has no units now
          // then it's a standoff
          if (!standoffsByProvince[province]) {
            standoffsByProvince[province] = {
              provID,
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
    standoffs = Object.values(standoffsByProvince) || [];

    // FIXME: Messy. After-the-fact, do some filtering of the standoffs to remove
    // some (but not all) of the above false positives. In particular, live phases
    // should always be correct, but historical phases that have two units that both
    // move to the same location via convoy where their convoy fails due to the intervening
    // fleets being dislodged will incorrectly depict a standoff there.
    if (isLivePhase) {
      const territoryStatusesByProvID = Object.fromEntries(
        data.data.territoryStatuses.map((territoryStatus) => [
          territoryStatus.id,
          territoryStatus,
        ]),
      );
      standoffs = standoffs.filter(
        (standoff) =>
          standoff.attemptedMoves.length >= 2 &&
          territoryStatusesByProvID[standoff.provID].standoff,
      );
    } else {
      standoffs = standoffs.filter(
        (standoff) => standoff.attemptedMoves.length >= 2,
      );
    }
  }

  const viewingGameFinishedPhase =
    overview.phase === "Finished" &&
    (viewedPhaseState.viewedPhaseIdx === status.phases.length - 1 ||
      status.phases.length === 0);
  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <WDMainController>
        <WDMapController
          units={units}
          phase={phase}
          orders={orders}
          maps={maps}
          territories={territories}
          centersByProvince={centersByProvince}
          standoffs={standoffs}
          isLivePhase={isLivePhase}
        />
        <WDUI
          orders={orders}
          viewingGameFinishedPhase={viewingGameFinishedPhase}
        />
      </WDMainController>
    </React.Suspense>
  );
};

export default WDMain;
