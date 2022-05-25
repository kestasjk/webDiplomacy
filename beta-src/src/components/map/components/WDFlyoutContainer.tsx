/* eslint-disable no-bitwise */
import * as React from "react";
import { Box, Button, Stack } from "@mui/material";
import Territories from "../../../data/Territories";
import {
  gameApiSliceActions,
  gameBoard,
  gameMaps,
  gameOrder,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import Territory from "../../../enums/map/variants/classic/Territory";
import { Unit } from "../../../utils/map/getUnits";
import isConvoyable from "../../../utils/state/isConvoyable";

import WDFlyoutButton from "./WDFlyoutButton";
import TerritoryMap from "../../../data/map/variants/classic/TerritoryMap";

interface WDFlyoutContainerProps {
  units: Unit[];
}

const WDFlyoutContainer: React.FC<WDFlyoutContainerProps> = function ({
  units,
}): React.ReactElement {
  const dispatch = useAppDispatch();
  const order = useAppSelector(gameOrder);
  const maps = useAppSelector(gameMaps);
  const board = useAppSelector(gameBoard);

  console.log({ order });

  if (!order.inProgress || order.type || !order.unitID) {
    return <Box />;
  }

  const unit = units.find((u) => u.unit.id === order.unitID);

  let territory = maps.unitToTerritory[order.unitID];
  const mTerr = TerritoryMap[territory];
  const { unitSlotName } = mTerr;
  if (mTerr.parent) {
    territory = mTerr.parent;
  }
  const clickHandler =
    (orderType, viaConvoy: string | undefined = undefined) =>
    () => {
      console.log(`Dispatched ${orderType}`);
      dispatch(
        gameApiSliceActions.updateOrder({
          type: orderType,
          viaConvoy,
        }),
      );
    };
  return (
    <>
      <WDFlyoutButton
        territory={territory}
        unitSlotName={unitSlotName}
        position="left"
        text="Hold"
        clickHandler={clickHandler("Hold")}
      />
      <WDFlyoutButton
        territory={territory}
        unitSlotName={unitSlotName}
        position="right"
        text="Move"
        clickHandler={clickHandler("Move")}
      />
      <WDFlyoutButton
        territory={territory}
        unitSlotName={unitSlotName}
        position="top"
        text="Support"
        clickHandler={clickHandler("Support")}
      />
      {(unit?.unit?.type === "Fleet" && (
        <WDFlyoutButton
          territory={territory}
          unitSlotName={unitSlotName}
          position="bottom"
          text="Convoy"
          clickHandler={clickHandler("Convoy")}
        />
      )) || <g />}
      {board && isConvoyable(board, unit) && (
        <WDFlyoutButton
          territory={territory}
          unitSlotName={unitSlotName}
          position="bottom"
          text="Via"
          clickHandler={clickHandler("Move", "Yes")}
        />
      )}
    </>
  );
};

export default WDFlyoutContainer;
