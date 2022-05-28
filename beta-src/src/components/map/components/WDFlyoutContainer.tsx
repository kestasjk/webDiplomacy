/* eslint-disable no-bitwise */
import * as React from "react";
import { Box, Button, Stack } from "@mui/material";
import {
  gameApiSliceActions,
  gameLegalOrders,
  gameMaps,
  gameOrder,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import Territory from "../../../enums/map/variants/classic/Territory";
import Province from "../../../enums/map/variants/classic/Province";
import { Unit } from "../../../utils/map/getUnits";

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
  const legalOrders = useAppSelector(gameLegalOrders);

  console.log("FLYOUT");
  console.log({ order });

  if (!order.inProgress || order.type || !order.unitID) {
    return <Box />;
  }

  const unit = units.find((u) => u.unit.id === order.unitID);

  const mTerr = TerritoryMap[maps.unitToTerritory[order.unitID]];
  const { province, unitSlotName } = mTerr;
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
        province={province}
        unitSlotName={unitSlotName}
        position="left"
        text="Hold"
        clickHandler={clickHandler("Hold")}
      />
      <WDFlyoutButton
        province={province}
        unitSlotName={unitSlotName}
        position="right"
        text="Move"
        clickHandler={clickHandler("Move")}
      />
      <WDFlyoutButton
        province={province}
        unitSlotName={unitSlotName}
        position="top"
        text="Support"
        clickHandler={clickHandler("Support")}
      />
      {(unit?.unit?.type === "Fleet" &&
        legalOrders.hasAnyLegalConvoysByUnitID[order.unitID] && (
          <WDFlyoutButton
            province={province}
            unitSlotName={unitSlotName}
            position="bottom"
            text="Convoy"
            clickHandler={clickHandler("Convoy")}
          />
        )) || <g />}
      {unit?.unit?.type === "Army" &&
        legalOrders.legalViasByUnitID[order.unitID].length > 0 && (
          <WDFlyoutButton
            province={province}
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
