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
import Province from "../../../enums/map/variants/classic/Province";
import provincesMapData from "../../../data/map/ProvincesMapData";

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

function getProvIDNumberOfTerrIDNumber(
  terrID: number,
  territories: APITerritories,
): number {
  if (territories[terrID]?.coastParentID) {
    return Number(territories[terrID].coastParentID);
  }
  return terrID;
}

function accumulateSupportHoldOrderArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  ordersByProvID: { [key: number]: IOrderDataHistorical },
  territories: APITerritories,
): void {
  // Maps supportee and supporter provIDs to help us find mutual supports.
  const supporterProvIDToSupporteeProvID: { [key: number]: number } = {};
  orders
    .filter((order) => order.type === "Support hold")
    .forEach((order) => {
      const provID = getProvIDNumberOfTerrIDNumber(order.terrID, territories);
      // Support orders toTerrID are always provinces
      const supporteeProvID = order.toTerrID;
      supporterProvIDToSupporteeProvID[provID] = supporteeProvID;
    });
  // console.log({ supporterProvIDToSupporteeProvID });

  orders
    .filter((order) => order.type === "Support hold")
    .forEach((order) => {
      if (!order.toTerrID) {
        return;
      }
      const supporterProvID = getProvIDNumberOfTerrIDNumber(
        order.terrID,
        territories,
      );
      const supporterTerr =
        TerritoryMap[territories[order.terrID].name].territory;

      // Support orders toTerrID are actually always provinces
      const supporteeProvID = order.toTerrID;
      const supporteeOrder = ordersByProvID[supporteeProvID];

      // If the supportee order is found at all, use it for the
      // supportee territory since it is a territory id whereas the supporter's
      // order might be province id.
      const supporteeTerr = supporteeOrder
        ? TerritoryMap[territories[supporteeOrder.terrID].name].territory
        : TerritoryMap[territories[order.toTerrID].name].territory;

      const arrowColor =
        order.success === "Yes"
          ? ArrowColor.SUPPORT_HOLD
          : ArrowColor.SUPPORT_HOLD_FAILED;

      // In case of a mutual support hold, offset the support line by a few pixels
      // so that the corresponding returning support line from the other order
      // doesn't overlap with it.
      const hasMutualSupport =
        supporterProvIDToSupporteeProvID[supporteeProvID] === supporterProvID;
      const offsetArrowSourcePixels = hasMutualSupport ? 6 : 0;
      // console.log({
      //   supporteeProvID,
      //   supporterProvID,
      //   hasMutualSupport,
      //   offsetArrowSourcePixels,
      // });

      arrows.push(
        drawArrowFunctional(
          ArrowType.HOLD,
          arrowColor,
          "unit",
          supporterTerr,
          "unit",
          supporteeTerr,
          offsetArrowSourcePixels,
        ),
      );
    });
}

function accumulateSupportMoveOrderArrows(
  arrows: (React.ReactElement | null)[],
  orders: IOrderDataHistorical[],
  ordersByProvID: { [key: number]: IOrderDataHistorical },
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
      // Support orders fromTerrID are actually always provinces
      const supporteeProvID = order.fromTerrID;
      const supporteeOrder = ordersByProvID[supporteeProvID];
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

      const arrowColor =
        order.success === "Yes"
          ? ArrowColor.SUPPORT_MOVE
          : ArrowColor.SUPPORT_MOVE_FAILED;

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
            arrowColor,
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
            arrowColor,
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
  ordersByProvID: { [key: number]: IOrderDataHistorical },
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
      // Convoyees are always armies, whose terrIDs and provIDs match
      const convoyeeOrder = ordersByProvID[order.fromTerrID];
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

      const arrowColor =
        order.success === "Yes" ? ArrowColor.CONVOY : ArrowColor.CONVOY_FAILED;

      const toTerr = TerritoryMap[territories[order.toTerrID].name].territory;
      arrows.push(
        drawArrowFunctional(
          ArrowType.CONVOY,
          arrowColor,
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
            ArrowColor.IMPLIED_FOREIGN,
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
          cx={x}
          cy={y}
          r={(1.4 * (w + h)) / 4}
          fill="none"
          stroke="rgb(0,150,0)"
          strokeWidth={0.05 * (w + h)}
        />,
      );
    });
}

function accumulateStandoffMarks(
  arrows: (React.ReactElement | null)[],
  standoffProvinces: Province[],
): void {
  standoffProvinces.forEach((province) => {
    const { rootTerritory } = provincesMapData[province];
    if (!rootTerritory) return;

    const [x1, y1, w1, h1] = getTargetXYWH("territory", rootTerritory);
    const MARKSIZE = 25;
    arrows.push(
      <g>
        <line
          key={`standoffmark-${province}-1`}
          x1={x1 - MARKSIZE}
          y1={y1 - MARKSIZE}
          x2={x1 + MARKSIZE}
          y2={y1 + MARKSIZE}
          stroke="red"
          strokeWidth={6}
        />
        <line
          key={`standoffmark-${province}-2`}
          x1={x1 + MARKSIZE}
          y1={y1 - MARKSIZE}
          x2={x1 - MARKSIZE}
          y2={y1 + MARKSIZE}
          stroke="red"
          strokeWidth={6}
        />
        <title>Cannot retreat here, standoff/bounce last turn</title>
      </g>,
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
  standoffProvinces: Province[];
}

const WDArrowContainer: React.FC<WDArrowProps> = function ({
  phase,
  orders,
  units,
  maps,
  territories,
  standoffProvinces,
}): React.ReactElement {
  const arrows: (React.ReactElement | null)[] = [];

  const ordersByProvID = {};
  orders.forEach((order) => {
    ordersByProvID[getProvIDNumberOfTerrIDNumber(order.terrID, territories)] =
      order;
  });
  accumulateMoveOrderArrows(arrows, orders, territories);
  accumulateSupportHoldOrderArrows(arrows, orders, ordersByProvID, territories);
  accumulateSupportMoveOrderArrows(arrows, orders, ordersByProvID, territories);
  accumulateConvoyOrderArrows(arrows, orders, ordersByProvID, territories);
  accumulateRetreatArrows(arrows, orders, territories);
  accumulateDislodgerArrows(arrows, units, territories);
  accumulateBuildCircles(arrows, units, territories);
  accumulateStandoffMarks(arrows, standoffProvinces);
  return <g id="arrows">{arrows}</g>;
};

export default WDArrowContainer;
