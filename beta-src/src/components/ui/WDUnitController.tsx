import * as React from "react";
import { gameIconProps } from "../../interfaces/Icons";
import UIState from "../../enums/UIState";
import debounce from "../../utils/debounce";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameApiSliceActions,
  gameData,
  gameOrder,
} from "../../state/game/game-api-slice";

interface UnitControllerProps {
  meta: gameIconProps["meta"];
  setIconState: React.Dispatch<UIState>;
}

const WDUnitController: React.FC<UnitControllerProps> = function ({
  children,
  setIconState,
  meta,
}): React.ReactElement {
  const dispatch = useAppDispatch();

  const commands = useAppSelector(
    (state) => state.game.commands.unitCommands[meta.unit.id],
  );

  const { data } = useAppSelector(gameData);

  const order = useAppSelector(gameOrder);

  if (order.unitID === meta.unit.id) {
    setIconState(UIState.SELECTED);
  } else {
    setIconState(UIState.NONE);
  }

  if (commands && commands.size > 0) {
    const firstCommand = commands.entries().next().value;
    if (firstCommand) {
      const [key, value] = firstCommand;
      switch (value.command) {
        case "HOLD":
          setIconState(UIState.HOLD);
          dispatch(
            gameApiSliceActions.deleteCommand({
              type: "unitCommands",
              id: meta.unit.id,
              command: key,
            }),
          );
          break;
        default:
          break;
      }
    }
  }

  let unitCanInitiateOrder = false;
  if ("currentOrders" in data) {
    const { currentOrders } = data;
    if (currentOrders) {
      const ordersLength = currentOrders.length;
      for (let i = 0; i < ordersLength; i += 1) {
        if (currentOrders[i].unitID === meta.unit.id) {
          unitCanInitiateOrder = true;
          break;
        }
      }
    }
  }

  const clickAction = function (e) {
    if (!unitCanInitiateOrder) {
      return;
    }
    dispatch(
      gameApiSliceActions.processUnitClick({
        onTerritory: meta.mappedTerritory.territory,
        unitID: meta.unit.id,
      }),
    );
  };

  const handleClick = debounce((e) => {
    clickAction(e);
  }, 200);

  const handleSingleClick = (e) => {
    handleClick[0](e);
  };

  const handleDoubleClick = (e) => {
    handleClick[1]();
    handleClick[0](e);
  };

  return (
    <g onClick={handleSingleClick} onDoubleClick={handleDoubleClick}>
      {children}
    </g>
  );
};

export default WDUnitController;
