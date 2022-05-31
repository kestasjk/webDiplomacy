/* eslint-disable no-bitwise */
import * as React from "react";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import { IOrderDataHistorical } from "../../../models/Interfaces";
import {
  gameApiSliceActions,
  gameOverview,
  gameTerritoriesMeta,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import GameStateMaps from "../../../state/interfaces/GameStateMaps";
import ArrowType from "../../../enums/ArrowType";
import ArrowColor from "../../../enums/ArrowColor";
import drawArrowFunctional, {
  getTargetXYWH,
  getArrowX1Y1X2Y2,
} from "../../../utils/map/drawArrowFunctional";
import TerritoryMap from "../../../data/map/variants/classic/TerritoryMap";
import { APITerritories } from "../../../state/interfaces/GameDataResponse";
import { Unit, UnitDrawMode } from "../../../utils/map/getUnits";

function accumulateMoveOrderArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  territories: APITerritories,
): void {
  // console.log("drawMoveOrders");
  orders
    .filter((order) => order.type === "Move")
    .forEach((order) => {
      if (!order.toTerrID) {
        return;
      }
      // console.log({
      //   order,
      //   territories,
      //   terrID: order.terrID,
      //   lookup: territories[order.terrID],
      // });
      const fromTerr = TerritoryMap[territories[order.terrID].name].territory;
      const toTerr = TerritoryMap[territories[order.toTerrID].name].territory;

      arrows.push(
        drawArrowFunctional(
          ArrowType.MOVE,
          order.success === "Yes" ? ArrowColor.MOVE : ArrowColor.MOVE_FAILED,
          "unit",
          fromTerr,
          "territory",
          toTerr,
        ),
      );
      // console.log("ARROW");
      // console.log(arrows[0]);

      if (order.viaConvoy === "Yes") {
        // TODO need to distinguish via vs nonvia orders??
      }
    });
}

function accumulateSupportHoldOrderArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  ordersByTerrID: { [key: number]: IOrderDataHistorical },
  ordersByCoastParentTerrID: { [key: number]: IOrderDataHistorical },
  territories: APITerritories,
): void {
  orders
    .filter((order) => order.type === "Support hold")
    .forEach((order) => {
      if (!order.toTerrID) {
        return;
      }
      const supporterTerr =
        TerritoryMap[territories[order.terrID].name].territory;

      const supporteeOrder =
        ordersByTerrID[order.toTerrID] ||
        ordersByCoastParentTerrID[order.toTerrID];

      // If the supportee order is found at all, use it for the
      // supportee territory since it is coast qualified whereas the supporter's
      // order does not have to be coast qualified.
      const supporteeTerr = supporteeOrder
        ? TerritoryMap[territories[supporteeOrder.terrID].name].territory
        : TerritoryMap[territories[order.toTerrID].name].territory;

      arrows.push(
        drawArrowFunctional(
          ArrowType.HOLD,
          ArrowColor.SUPPORT_HOLD,
          "unit",
          supporterTerr,
          "unit",
          supporteeTerr,
        ),
      );
    });
}

function accumulateSupportMoveOrderArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  ordersByTerrID: { [key: number]: IOrderDataHistorical },
  ordersByCoastParentTerrID: { [key: number]: IOrderDataHistorical },
  territories: APITerritories,
): void {
  orders
    .filter((order) => order.type === "Support move")
    .forEach((order) => {
      if (!(order.fromTerrID && order.toTerrID)) {
        return;
      }

      const supporterTerr =
        TerritoryMap[territories[order.terrID].name].territory;
      let isCoordinated = false;
      const supporteeOrder =
        ordersByTerrID[order.fromTerrID] ||
        ordersByCoastParentTerrID[order.fromTerrID];
      if (
        supporteeOrder &&
        supporteeOrder.type === "Move" &&
        (supporteeOrder.terrID === order.fromTerrID ||
          territories[supporteeOrder.terrID].coastParentID ===
            order.fromTerrID.toString()) &&
        (supporteeOrder.toTerrID === order.toTerrID ||
          territories[supporteeOrder.toTerrID].coastParentID ===
            order.toTerrID.toString())
      ) {
        isCoordinated = true;
      }

      // If the supportee order is found at all, use it for the
      // supportee territory since it is coast qualified whereas the supporter's
      // order does not have to be coast qualified.
      const supporteeTerr = supporteeOrder
        ? TerritoryMap[territories[supporteeOrder.terrID].name].territory
        : TerritoryMap[territories[order.fromTerrID].name].territory;

      if (isCoordinated) {
        // For coordinated supports, use the order for the supportee for determining
        // the destination location because the destination of the supportee order
        // must be coast-qualified whereas the locations of the supporter order
        // does not have to be coast-qualified.
        const toTerr =
          TerritoryMap[territories[supporteeOrder.toTerrID].name].territory;

        arrows.push(
          drawArrowFunctional(
            ArrowType.SUPPORT,
            ArrowColor.SUPPORT_MOVE,
            "unit",
            supporterTerr,
            "arrow",
            getArrowX1Y1X2Y2("unit", supporteeTerr, "territory", toTerr),
          ),
        );
      } else {
        // Uncoordinated supports
        const toTerr = TerritoryMap[territories[order.toTerrID].name].territory;
        arrows.push(
          drawArrowFunctional(
            ArrowType.SUPPORT,
            ArrowColor.SUPPORT_MOVE,
            "unit",
            supporterTerr,
            "arrow",
            getArrowX1Y1X2Y2("unit", supporteeTerr, "territory", toTerr),
          ),
        );
        // Also draw a ghosty arrow of what we're trying to support.
        arrows.push(
          drawArrowFunctional(
            ArrowType.MOVE,
            ArrowColor.IMPLIED_FOREIGN,
            "unit",
            supporteeTerr,
            "territory",
            toTerr,
          ),
        );
      }
    });
}

function accumulateConvoyOrderArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  ordersByTerrID: { [key: number]: IOrderDataHistorical },
  territories: APITerritories,
): void {
  orders
    .filter((order) => order.type === "Convoy")
    .forEach((order) => {
      if (!(order.fromTerrID && order.toTerrID)) {
        return;
      }

      const convoyerTerr =
        TerritoryMap[territories[order.terrID].name].territory;
      const convoyeeTerr =
        TerritoryMap[territories[order.fromTerrID].name].territory;
      let isCoordinated = false;
      const convoyeeOrder = ordersByTerrID[order.fromTerrID];
      if (
        convoyeeOrder &&
        convoyeeOrder.type === "Move" &&
        convoyeeOrder.terrID === order.fromTerrID &&
        (convoyeeOrder.toTerrID === order.toTerrID ||
          territories[convoyeeOrder.toTerrID].coastParentID ===
            order.toTerrID.toString())
      ) {
        isCoordinated = true;
      }

      const toTerr = TerritoryMap[territories[order.toTerrID].name].territory;
      arrows.push(
        drawArrowFunctional(
          ArrowType.CONVOY,
          ArrowColor.CONVOY,
          "unit",
          convoyerTerr,
          "arrow",
          getArrowX1Y1X2Y2("unit", convoyeeTerr, "territory", toTerr),
        ),
      );
      if (!isCoordinated) {
        // Also draw a ghosty arrow of what we're trying to convoy.
        arrows.push(
          drawArrowFunctional(
            ArrowType.MOVE,
            ArrowColor.IMPLIED,
            "unit",
            convoyeeTerr,
            "territory",
            toTerr,
          ),
        );
      }
    });
}

function accumulateRetreatArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  territories: APITerritories,
): void {
  orders
    .filter((order) => order.type === "Retreat")
    .forEach((order) => {
      if (!order.toTerrID) {
        return;
      }
      const fromTerr = TerritoryMap[territories[order.terrID].name].territory;
      const toTerr = TerritoryMap[territories[order.toTerrID].name].territory;

      arrows.push(
        drawArrowFunctional(
          ArrowType.MOVE,
          ArrowColor.RETREAT,
          "unit",
          fromTerr,
          "territory",
          toTerr,
        ),
      );
    });
}

function accumulateDislodgerArrows(
  arrows: (React.ReactElement | null)[],
  units: Unit[],
  territories: APITerritories,
): void {
  units
    .filter((unit) => unit.drawMode === UnitDrawMode.DISLODGING)
    .forEach((unit) => {
      if (unit.movedFromTerrID === null) return;
      const fromTerr =
        TerritoryMap[territories[unit.movedFromTerrID].name].territory;
      const toTerr = TerritoryMap[territories[unit.unit.terrID].name].territory;

      arrows.push(
        drawArrowFunctional(
          ArrowType.MOVE,
          ArrowColor.MOVE,
          "territory",
          fromTerr,
          "dislodger",
          toTerr,
        ),
      );
    });
}

// This isn't exactly an arrow, but...
function accumulateBuildCircles(
  arrows: (React.ReactElement | null)[],
  units: Unit[],
  territories: APITerritories,
): void {
  units
    .filter((unit) => unit.drawMode === UnitDrawMode.BUILD)
    .forEach((unit) => {
      const terr = TerritoryMap[territories[unit.unit.terrID].name].territory;
      const [x, y, w, h] = getTargetXYWH("unit", terr);

      arrows.push(
        <circle
          key={`build-circle-${terr}`}
          cx={x + w / 2}
          cy={y + h / 2}
          r={10 + (w + h) / 4}
          fill="none"
          stroke="rgb(0,150,0)"
          strokeWidth={6}
        />,
      );
    });
}
/*
export interface IOrderDataHistorical {
  countryID: string;
  dislodged: string;
  fromTerrID: number;
  phase: string;
  success: string;
  terrID: number;
  toTerrID: number;
  turn: number;
  type: string;
  unitType: string;
  viaConvoy: string;
}
*/

interface WDArrowProps {
  phase: string;
  orders: IOrderDataHistorical[];
  units: Unit[];
  maps: GameStateMaps;
  territories: APITerritories;
}

const WDArrowContainer: React.FC<WDArrowProps> = function ({
  phase,
  orders,
  units,
  maps,
  territories,
}): React.ReactElement {
  const arrows: (React.ReactElement | null)[] = [];

  const ordersByTerrID = {};
  orders.forEach((order) => {
    ordersByTerrID[order.terrID] = order;
  });
  const ordersByCoastParentTerrID = {};
  orders.forEach((order) => {
    if (territories[order.terrID]?.coastParentID)
      ordersByCoastParentTerrID[territories[order.terrID].coastParentID] =
        order;
  });
  accumulateMoveOrderArrows(arrows, orders, territories);
  accumulateSupportHoldOrderArrows(
    arrows,
    orders,
    ordersByTerrID,
    ordersByCoastParentTerrID,
    territories,
  );
  accumulateSupportMoveOrderArrows(
    arrows,
    orders,
    ordersByTerrID,
    ordersByCoastParentTerrID,
    territories,
  );
  accumulateConvoyOrderArrows(arrows, orders, ordersByTerrID, territories);
  accumulateRetreatArrows(arrows, orders, territories);
  accumulateDislodgerArrows(arrows, units, territories);
  accumulateBuildCircles(arrows, units, territories);
  return <g id="arrows">{arrows}</g>;
};

export default WDArrowContainer;
