import * as React from "react";
import { GameIconProps } from "../../interfaces/Icons";
import UIState from "../../enums/UIState";
import debounce from "../../utils/debounce";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import { gameApiSliceActions } from "../../state/game/game-api-slice";
import processNextCommand from "../../utils/processNextCommand";
import WDFleetIcon from "../ui/units/WDFleetIcon";
import WDArmyIcon from "../ui/units/WDArmyIcon";

interface UnitControllerProps {
  meta: GameIconProps["meta"];
  initialIconState: UIState;
  type: GameIconProps["type"];
}

const WDUnitController: React.FC<UnitControllerProps> = function ({
  initialIconState,
  meta,
  type,
}): React.ReactElement {
  const dispatch = useAppDispatch();
  const [iconState, setIconState] = React.useState(initialIconState);

  const commands = useAppSelector(
    (state) => state.game.commands.unitCommands[meta.unit.id],
  );

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
    DISLODGED: (command) => {
      const [key] = command;
      setIconState(UIState.DISLODGED);
      deleteCommand(key);
    },
    DESTROY: (command) => {
      const [key] = command;
      setIconState(UIState.DESTROY);
      deleteCommand(key);
    },
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
    DISBAND: (command) => {
      const [key] = command;
      setIconState(UIState.DISBANDED);
      deleteCommand(key);
    },
  };

  processNextCommand(commands, commandActions);

  const clickAction = (e, method) => {
    dispatch(
      gameApiSliceActions.processUnitClick({
        method,
        onTerritory: meta.mappedTerritory.territory,
        unitID: meta.unit.id,
      }),
    );
  };

  const handleClick = debounce((e) => {
    clickAction(e, "click");
  }, 200);

  const handleSingleClick = (e) => {
    handleClick[0](e);
  };

  const handleDoubleClick = (e) => {
    handleClick[1]();
    clickAction(e, "dblClick");
  };

  return (
    <g onClick={handleSingleClick} onDoubleClick={handleDoubleClick}>
      {type === "Fleet" && (
        <WDFleetIcon iconState={iconState} country={meta.country} />
      )}
      {type === "Army" && (
        <WDArmyIcon iconState={iconState} country={meta.country} />
      )}
    </g>
  );
};

export default WDUnitController;
