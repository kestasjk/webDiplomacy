import * as React from "react";
import { Stack, useTheme } from "@mui/material";
import WDButton from "./WDButton";
import Move from "../../enums/Move";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import Device from "../../enums/Device";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameApiSliceActions,
  gameData,
  gameOrdersMeta,
  gameOverview,
  saveOrders,
} from "../../state/game/game-api-slice";
import UpdateOrder from "../../interfaces/state/UpdateOrder";
import MoveStatus from "../../types/MoveStatus";
import { RootState } from "../../state/store";
import { OrderStatus } from "../../interfaces/state/MemberData";

interface WDMoveControlsProps {
  orderStatus: OrderStatus;
}

const WDMoveControls: React.FC<WDMoveControlsProps> = function ({
  orderStatus,
}): React.ReactElement {
  const theme = useTheme();
  const [viewport] = useViewport();
  const { data } = useAppSelector(gameData);
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const currentOrderInProgress = useAppSelector(
    ({ game: { order } }: RootState) => order.inProgress,
  );
  const [readyDisabled, setReadyDisabled] = React.useState(false);
  const [gameState, setGameState] = React.useState<MoveStatus>({
    save: false,
    ready: false,
  });

  if (orderStatus.None && !readyDisabled) {
    setReadyDisabled(true);
  }

  React.useEffect(() => {
    if (orderStatus.Ready !== gameState.ready) {
      setGameState((preState) => ({
        ...preState,
        [Move.READY]: orderStatus.Ready,
      }));
    }
  }, [orderStatus]);

  const toggleState = (move: Move) => {
    setGameState((preState) => ({
      ...preState,
      [move]: !gameState[move],
    }));
  };

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
    // console.log("Entered save button click");
    // When you click save or ready, it should clear any actively entered order you have going,
    // and/or any of the move input flyover. It doesn't make sense to ready and have the UI
    // stay with a partially-entered order.
    dispatch(gameApiSliceActions.resetOrder());

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
              type: moveType || "",
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
          orderSubmission.queryParams = gameState.ready
            ? { notready: "on" }
            : { ready: "on" };
        }
        // console.log({ orderSubmission });
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

  if (
    (ordersLength === ordersSaved && gameState.save) ||
    (ordersLength !== ordersSaved && !gameState.save)
  ) {
    toggleState(Move.SAVE);
  }

  const saveDisabled = gameState.ready || !gameState.save;
  const doAnimateGlow =
    !saveDisabled && ordersLength !== ordersSaved && !currentOrderInProgress;

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
        doAnimateGlow={doAnimateGlow}
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
        {gameState.ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDMoveControls;
