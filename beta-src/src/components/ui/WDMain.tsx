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
        units,
        orders: ordersHistorical,
        centersByProvince,
        isLatestPhase: true,
      };
    }

    const phaseHistorical = status.phases[viewedPhaseState.viewedPhaseIdx];
    if (!phaseHistorical) {
      return {
        phase: "",
        units: [],
        orders: [],
        centersByProvince: {},
        isLatestPhase: true,
      };
    }

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
          isLatestPhase={isLatestPhase}
        />
        <WDUI orders={orders} />
      </WDMainController>
    </React.Suspense>
  );
};

export default WDMain;
