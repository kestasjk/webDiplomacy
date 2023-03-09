import React from "react";
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

import WDMapController from "../controllers/WDMapController";
import getPhaseKey from "../../utils/state/getPhaseKey";

const WDMain: React.FC = function (): React.ReactElement {
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

  const updateForPhase = () => {
    // Only do live viewing if game is not over and not spectating
    const isPlayingGame =
      overview.phase !== "Finished" &&
      status.status === "Playing" &&
      !!overview.user;
    if (
      viewedPhaseState.viewedPhaseIdx >= status.phases.length - 1 &&
      isPlayingGame &&
      overview.user &&
      getPhaseKey(status.phases[status.phases.length - 1], "<BAD1>") ===
        getPhaseKey(overview, "<BAD2>") // check that we're not in an intermediate loading state
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
        const drawAsUnsaved = !orderMeta.saved;

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
          drawAsUnsaved,
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
        data.data.isSandboxMode,
      );

      const centersByProvince: { [key: string]: { ownerCountryID: string } } =
        {};
      data.data.territoryStatuses.forEach((provinceStatus) => {
        const province = maps.terrIDToProvince[provinceStatus.id];
        const ownerCountryID = provinceStatus.ownerCountryID || "0";
        centersByProvince[province] = { ownerCountryID };
      });

      return {
        phase: overview.phase, // status.phases[viewedPhaseState.viewedPhaseIdx].phase,
        units,
        orders: ordersHistorical,
        centersByProvince,
        isLivePhase: true,
      };
    }

    if (!status.phases[viewedPhaseState.viewedPhaseIdx]) {
      return {
        phase: "",
        units: [],
        orders: [],
        centersByProvince: {},
        isLivePhase: isPlayingGame,
      };
    }
    const phaseHistorical = status.phases[viewedPhaseState.viewedPhaseIdx];
    const unitsHistorical = phaseHistorical.units;
    const prevPhaseOrders =
      viewedPhaseState.viewedPhaseIdx > 0
        ? status.phases[viewedPhaseState.viewedPhaseIdx - 1].orders
        : [];
    const units = getUnitsHistorical(
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

    const phase = phaseHistorical.phase as string;

    // On historical build phases, the API doesn't report the unit type of
    // destroyed units! So we manually fill those in ourselves
    let { orders } = phaseHistorical;
    if (phase === "Builds") {
      orders = orders.map((order) => {
        if (order.type !== "Destroy") return order;
        const foundUnit = units.find(
          (unit) =>
            unit.mappedTerritory.province ===
            maps.terrIDToProvince[order.terrID],
        );
        if (!foundUnit) return order;
        return { ...order, unitType: foundUnit.unit.type };
      });
    }

    return {
      phase,
      units,
      orders,
      centersByProvince,
      isLivePhase: false,
    };
  };

  const { phase, units, orders, centersByProvince, isLivePhase } =
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
        const provID = maps.terrIDToProvinceID[order.toTerrID];
        if (!provID) {
          // happens if maps hasn't been loaded yet.
          return;
        }
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
        units={units}
        viewingGameFinishedPhase={viewingGameFinishedPhase}
      />
    </WDMainController>
  );
};

export default WDMain;
