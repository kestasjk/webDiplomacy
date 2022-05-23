/* eslint-disable no-bitwise */
import * as React from "react";
import { Box, Button, Stack } from "@mui/material";
import Territories from "../../../data/Territories";
import {
  gameApiSliceActions,
  gameFlyoutMenu,
  gameMaps,
  gameOrder,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import WDBuildUnitButtons from "./WDBuildUnitButtons";
import Territory from "../../../enums/map/variants/classic/Territory";
import WDFlyoutButton from "./WDFlyoutButton";

const WDFlyoutMenu: React.FC = function (): React.ReactElement {
  const dispatch = useAppDispatch();
  const order = useAppSelector(gameOrder);
  const maps = useAppSelector(gameMaps);
  console.log({ order });

  if (!order.inProgress || order.type || !order.unitID) {
    return <Box />;
  }

  const territory = maps.terrIDToTerritory[maps.unitToTerrID[order.unitID]];
  const unitSlotName = "main"; // FIXME
  const clickHandler = (orderType) => () => {
    console.log(`Dispatched ${orderType}`);
    dispatch(
      gameApiSliceActions.updateOrder({
        type: orderType,
      }),
    );
    dispatch(
      gameApiSliceActions.updateOrdersMeta({
        [order.orderID]: {
          saved: false,
          update: {
            type: orderType,
            toTerrID: null,
          },
        },
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
        clickHandler={clickHandler("hold")}
      />
      <WDFlyoutButton
        territory={territory}
        unitSlotName={unitSlotName}
        position="right"
        text="Move"
        clickHandler={clickHandler("move")}
      />
      <WDFlyoutButton
        territory={territory}
        unitSlotName={unitSlotName}
        position="top"
        text="Support"
        clickHandler={clickHandler("support")}
      />
      <WDFlyoutButton
        territory={territory}
        unitSlotName={unitSlotName}
        position="bottom"
        text="Convoy"
        clickHandler={clickHandler("convoy")}
      />
    </>
  );
};

export default WDFlyoutMenu;
