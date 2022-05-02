import * as React from "react";
import { GameIconProps } from "../../interfaces/Icons";
import UIState from "../../enums/UIState";
import debounce from "../../utils/debounce";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import { gameApiSliceActions, gameData } from "../../state/game/game-api-slice";
import processNextCommand from "../../utils/processNextCommand";

interface UnitControllerProps {
  meta: GameIconProps["meta"];
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

  const deleteCommand = (key) => {
    dispatch(
      gameApiSliceActions.deleteCommand({
        type: "unitCommands",
        id: meta.unit.id,
        command: key,
      }),
    );
  };

  const commandActions = {
    HOLD: (command) => {
      const [key] = command;
      setIconState(UIState.HOLD);
      deleteCommand(key);
    },
    NONE: (command) => {
      const [key] = command;
      setIconState(UIState.NONE);
      deleteCommand(key);
    },
    SELECTED: (command) => {
      const [key] = command;
      setIconState(UIState.SELECTED);
      deleteCommand(key);
    },
  };

  processNextCommand(commands, commandActions);

  let unitCanInitiateOrder = false;
  if ("currentOrders" in data) {
    const { currentOrders } = data;
    if (currentOrders) {
      for (let i = 0; i < currentOrders.length; i += 1) {
        if (currentOrders[i].unitID === meta.unit.id) {
          unitCanInitiateOrder = true;
          break;
        }
      }
    }
  }

  const clickAction = (e) => {
    if (!unitCanInitiateOrder) {
      return;
    }
    dispatch(
      gameApiSliceActions.processUnitClick({
        method: "click",
        onTerritory: meta.mappedTerritory.territory,
        unitID: meta.unit.id,
      }),
    );
  };

  const doubleClickAction = (e) => {
    if (!unitCanInitiateOrder) {
      return;
    }
    dispatch(
      gameApiSliceActions.processUnitDoubleClick({
        method: "dblClick",
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
    doubleClickAction(e);
  };

  return (
    <g onClick={handleSingleClick} onDoubleClick={handleDoubleClick}>
      {children}
    </g>
  );
};

export default WDUnitController;
