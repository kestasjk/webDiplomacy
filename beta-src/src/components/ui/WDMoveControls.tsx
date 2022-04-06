import * as React from "react";
import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import MoveStatus from "../../types/MoveStatus";
import Move from "../../enums/Move";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import Device from "../../enums/Device";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameData,
  gameOrdersMeta,
  saveOrders,
} from "../../state/game/game-api-slice";
import { IOrderData } from "../../models/Interfaces";

interface WDMoveControlsProps {
  gameState: MoveStatus;
  toggleState: (move: Move) => void;
}

const WDMoveControls: React.FC<WDMoveControlsProps> = function ({
  gameState: { ready, save },
  toggleState,
}): React.ReactElement {
  const [viewport] = useViewport();
  const { data } = useAppSelector(gameData);
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const dispatch = useAppDispatch();
  const device = getDevice(viewport);
  let isMobile: boolean;
  switch (device) {
    case Device.MOBILE:
    case Device.MOBILE_LG:
    case Device.MOBILE_LANDSCAPE:
    case Device.MOBILE_LG_LANDSCAPE:
      isMobile = true;
      break;
    default:
      isMobile = false;
      break;
  }

  const click = () => {
    if ("currentOrders" in data && "contextVars" in data) {
      const { currentOrders, contextVars } = data;
      if (contextVars && currentOrders) {
        const orderUpdates: IOrderData[] = [];
        currentOrders.forEach((o) => {
          const updateReference = ordersMeta[o.id].update;
          let orderUpdate: IOrderData = o;
          if (updateReference) {
            orderUpdate = {
              ...o,
              ...{
                type: updateReference.type,
                toTerrID: updateReference.toTerrID,
              },
            };
          }
          orderUpdates.push(orderUpdate);
        });
        const orderSubmission = {
          orderUpdates,
          context: contextVars.context,
          contextKey: contextVars.contextKey,
        };
        dispatch(saveOrders(orderSubmission));
      }
    }
  };

  const ordersMetaValues = Object.values(ordersMeta);
  const ordersLength = ordersMetaValues.length;
  const ordersSaved = ordersMetaValues.reduce(
    (acc, meta) => acc + +meta.saved,
    0,
  );

  if (
    (ordersLength === ordersSaved && save) ||
    (ordersLength !== ordersSaved && !save)
  ) {
    toggleState(Move.SAVE);
  }

  return (
    <Stack
      alignItems="center"
      direction={isMobile ? "column" : "row"}
      spacing={2}
    >
      <WDButton color="primary" disabled={ready || !save} onClick={click}>
        Save
      </WDButton>
      <WDButton color="primary" onClick={() => toggleState(Move.READY)}>
        {ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDMoveControls;
