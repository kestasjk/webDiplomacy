import * as React from "react";
import { Stack, useTheme } from "@mui/material";
import WDButton from "./WDButton";
import MoveStatus from "../../types/MoveStatus";
import Move from "../../enums/Move";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import Device from "../../enums/Device";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import gameApiSlice, {
  gameApiSliceActions,
  gameData,
  gameOrdersMeta,
  saveOrders,
} from "../../state/game/game-api-slice";
import UpdateOrder from "../../interfaces/state/UpdateOrder";

interface WDMoveControlsProps {
  gameState: MoveStatus;
  readyDisabled: boolean;
  toggleState: (move: Move) => void;
}

const WDMoveControls: React.FC<WDMoveControlsProps> = function ({
  gameState: { ready, save },
  readyDisabled,
  toggleState,
}): React.ReactElement {
  const theme = useTheme();
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

  const clickButton = (type: Move) => {
    dispatch(
      gameApiSliceActions.processMapClick({
        name: undefined,
        clickObject: "save_button",
      }),
    );
    if ("currentOrders" in data && "contextVars" in data) {
      const { currentOrders, contextVars } = data;
      if (contextVars && currentOrders) {
        const orderUpdates: UpdateOrder[] = [];
        currentOrders.forEach(
          ({ fromTerrID, id, toTerrID, type: moveType, unitID, viaConvoy }) => {
            const updateReference = ordersMeta[id].update;
            let orderUpdate: UpdateOrder = {
              fromTerrID,
              id,
              toTerrID,
              type: moveType,
              unitID,
              viaConvoy,
            };
            if (updateReference) {
              orderUpdate = {
                ...orderUpdate,
                ...updateReference,
              };
            }
            orderUpdates.push(orderUpdate);
          },
        );
        const orderSubmission = {
          orderUpdates,
          context: JSON.stringify(contextVars.context),
          contextKey: contextVars.contextKey,
          queryParams: {},
        };

        if (type === Move.READY) {
          orderSubmission.queryParams = ready
            ? { notready: "on" }
            : { ready: "on" };
        }
        dispatch(saveOrders(orderSubmission));
      }
    }

    if (type === Move.READY) {
      toggleState(type);
    }
  };

  const ordersMetaValues = Object.values(ordersMeta);
  const ordersLength = ordersMetaValues.length;
  const ordersSaved = ordersMetaValues.reduce(
    (acc, meta) => acc + +meta.saved,
    0,
  );

  // if (ordersSaved) {
  //   const disbandingOrdersSaved = ordersMetaValues.filter(
  //     (val) => val.saved && val.update && val.update.type === "Disband",
  //   );
  //   if (disbandingOrdersSaved?.length) {
  //     dispatch(gameApiSliceActions.updateUnitsDisbanding());
  //   }
  // }

  if (
    (ordersLength === ordersSaved && save) ||
    (ordersLength !== ordersSaved && !save)
  ) {
    toggleState(Move.SAVE);
  }

  const saveDisabled = ready || !save;

  return (
    <Stack
      alignItems="center"
      direction={isMobile ? "column" : "row"}
      spacing={2}
    >
      <WDButton
        color="primary"
        disabled={saveDisabled}
        onClick={() => clickButton(Move.SAVE)}
        sx={{
          filter: saveDisabled
            ? undefined
            : theme.palette.svg.filters.dropShadows[0],
        }}
      >
        Save
      </WDButton>
      <WDButton
        color="primary"
        disabled={readyDisabled}
        onClick={() => clickButton(Move.READY)}
        sx={{
          filter: readyDisabled
            ? undefined
            : theme.palette.svg.filters.dropShadows[0],
        }}
      >
        {ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDMoveControls;
