import React, { useEffect } from "react";
import useLocalStorageState from "use-local-storage-state";
import WDButton from "./WDButton";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameApiSliceActions,
  gameData,
  gameOrdersMeta,
  gameStatus,
  gameViewedPhase,
  saveOrders,
} from "../../state/game/game-api-slice";
import UpdateOrder from "../../interfaces/state/UpdateOrder";
import { RootState } from "../../state/store";
import { OrderStatus } from "../../interfaces/state/MemberData";
import OrderSubmission from "../../interfaces/state/OrderSubmission";

enum OrderStatusButton {
  SAVE = "save",
  READY = "ready",
}

interface WDOrderStatsControlsProps {
  orderStatus: OrderStatus;
}

const WDOrderStatusControls: React.FC<WDOrderStatsControlsProps> = function ({
  orderStatus,
}): React.ReactElement {
  // This is here because I have the feeling that there is not a consensus about the auto-save feature yet.
  // We might have to have this in the back-end
  const [settings] = useLocalStorageState("settings", {
    defaultValue: { autoSave: true },
  });

  const { data } = useAppSelector(gameData);
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const status = useAppSelector(gameStatus);
  const viewedPhaseState = useAppSelector(gameViewedPhase);
  const savingOrdersInProgress = useAppSelector(
    (state) => state.game.savingOrdersInProgress,
  );

  const viewingCurPhase =
    viewedPhaseState.viewedPhaseIdx >= status.phases.length - 1;

  const currentOrderInProgress = useAppSelector(
    ({ game: { order } }: RootState) => order.inProgress,
  );

  const dispatch = useAppDispatch();

  const ordersMetaValues = Object.values(ordersMeta);
  const ordersLength = ordersMetaValues.length;
  const ordersSaved = ordersMetaValues.reduce(
    (acc, meta) => acc + +meta.saved,
    0,
  );

  let readyEnabled: boolean;
  let saveEnabled: boolean;
  let readyButtonText: string;
  let saveButtonText: string;
  const saveText = "Save";

  // orderStatus contains what the server thinks our order status is.
  if (savingOrdersInProgress === "readying") {
    readyEnabled = false;
    saveEnabled = false;
    readyButtonText = "Readying...";
    saveButtonText = saveText;
  } else if (savingOrdersInProgress === "unreadying") {
    readyEnabled = false;
    saveEnabled = false;
    readyButtonText = "Unreadying...";
    saveButtonText = saveText;
  } else if (savingOrdersInProgress === "saving") {
    readyEnabled = false;
    saveEnabled = false;
    readyButtonText = "Ready";
    saveButtonText = "Saving...";
  } else if (orderStatus.Ready) {
    readyEnabled = viewingCurPhase;
    saveEnabled = false;
    readyButtonText = "Unready";
    saveButtonText = saveText;
  } else if (orderStatus.Saved) {
    readyEnabled = viewingCurPhase;
    saveEnabled = ordersLength !== ordersSaved && viewingCurPhase;
    readyButtonText = "Ready";
    saveButtonText = saveText;
  } else if (orderStatus.Completed) {
    readyEnabled = ordersLength !== ordersSaved && viewingCurPhase;
    saveEnabled = ordersLength !== ordersSaved && viewingCurPhase;
    readyButtonText = "Ready";
    saveButtonText = saveText;
  } else {
    readyEnabled = ordersLength !== ordersSaved && viewingCurPhase;
    saveEnabled = viewingCurPhase;
    readyButtonText = "Ready";
    saveButtonText = saveText;
  }

  const doAnimateGlow =
    saveEnabled && ordersLength !== ordersSaved && !currentOrderInProgress;

  const clickButton = (whatButton: OrderStatusButton) => {
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
        const orderSubmission: OrderSubmission = {
          orderUpdates,
          context: JSON.stringify(contextVars.context),
          contextKey: contextVars.contextKey,
          queryParams: {},
          userIntent: "saving",
        };
        if (whatButton === OrderStatusButton.READY) {
          if (orderStatus.Ready) {
            orderSubmission.queryParams = { notready: "on" };
            orderSubmission.userIntent = "unreadying";
          } else {
            orderSubmission.queryParams = { ready: "on" };
            orderSubmission.userIntent = "readying";
          }
        }
        // console.log({ orderSubmission });
        dispatch(saveOrders(orderSubmission));
      }
    }
  };

  useEffect(() => {
    const needsToSave = Object.keys(ordersMeta).some(
      (key) => ordersMeta[key].saved === false,
    );
    if (needsToSave && saveEnabled && settings.autoSave) {
      clickButton(OrderStatusButton.SAVE);
    }
  }, [ordersMeta]);

  const buttonClass = "w-14 h-14 rounded-full sm:w-fit sm:px-[30px]";

  return (
    <div className="flex flex-col sm:flex-row justify-end space-y-2 space-x-0 sm:space-x-3 sm:space-y-0 w-fit">
      {!settings.autoSave && (
        <WDButton
          color="primary"
          className={buttonClass}
          disabled={!saveEnabled}
          onClick={() => saveEnabled && clickButton(OrderStatusButton.SAVE)}
          doAnimateGlow={doAnimateGlow}
        >
          {saveButtonText}
        </WDButton>
      )}
      <WDButton
        color="primary"
        className={buttonClass}
        disabled={!readyEnabled}
        onClick={() => readyEnabled && clickButton(OrderStatusButton.READY)}
      >
        {readyButtonText}
      </WDButton>
    </div>
  );
};

export default WDOrderStatusControls;
