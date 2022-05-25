/* eslint-disable no-bitwise */
import * as React from "react";
import { Box, Button, Stack } from "@mui/material";
import Territories from "../../../data/Territories";
import {
  gameApiSliceActions,
  gameMaps,
  gameOrder,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import Territory from "../../../enums/map/variants/classic/Territory";
import { Unit } from "../../../utils/map/getUnits";
import WDFlyoutButton from "./WDFlyoutButton";

interface WDFlyoutContainerProps {
  units: Unit[];
}

const WDFlyoutContainer: React.FC<WDFlyoutContainerProps> = function ({
  units,
}): React.ReactElement {
  const dispatch = useAppDispatch();
  const order = useAppSelector(gameOrder);
  const maps = useAppSelector(gameMaps);

  console.log({ order });

  if (!order.inProgress || order.type || !order.unitID) {
    return <Box />;
  }

  const unit = units.find((u) => u.unit.id === order.unitID);

  const territory = maps.terrIDToTerritory[maps.unitToTerrID[order.unitID]];
  const unitSlotName = "main"; // FIXME
  const clickHandler = (orderType) => () => {
    console.log(`Dispatched ${orderType}`);
    dispatch(
      gameApiSliceActions.updateOrder({
        type: orderType,
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
    </>
  );
};

export default WDFlyoutContainer;
