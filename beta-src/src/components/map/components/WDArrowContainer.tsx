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
import drawArrowFunctional from "../../../utils/map/drawArrowFunctional";
import TerritoryMap from "../../../data/map/variants/classic/TerritoryMap";
import { APITerritories } from "../../../state/interfaces/GameDataResponse";

function drawMoveOrders(
  orders: IOrderDataHistorical[],
  maps: GameStateMaps,
  arrows: (React.ReactElement | null)[],
  territories: APITerritories,
): void {
  console.log("drawMoveOrders");
  orders
    .filter((order) => order.type === "Move")
    .forEach((order) => {
      if (order.toTerrID) {
        const fromTerrDetails = territories[order.terrID];
        const toTerrDetails = territories[order.toTerrID];
        const fromTerr = TerritoryMap[fromTerrDetails.name].territory;
        const toTerr = TerritoryMap[toTerrDetails.name].territory;

        arrows.push(
          drawArrowFunctional(
            ArrowType.MOVE,
            order.success === "Yes" ? ArrowColor.MOVE : ArrowColor.MOVE_FAILED,
            "territory",
            toTerr,
            fromTerr,
          ),
        );
        if (order.viaConvoy === "Yes") {
          // TODO
        }
      }
    });
}

interface WDArrowProps {
  phase: string;
  orders: IOrderDataHistorical[];
  maps: GameStateMaps;
  territories: APITerritories;
}

const WDArrowContainer: React.FC<WDArrowProps> = function ({
  phase,
  orders,
  maps,
  territories,
}): React.ReactElement {
  const arrows: (React.ReactElement | null)[] = [];
  drawMoveOrders(orders, maps, arrows, territories);
  return <g id="arrows">{arrows}</g>;
};

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

export default WDArrowContainer;
