import * as React from "react";
import { GameIconProps } from "../../interfaces/Icons";
import debounce from "../../utils/debounce";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import { gameApiSliceActions } from "../../state/game/game-api-slice";
import WDFleetIcon from "../ui/units/WDFleetIcon";
import WDArmyIcon from "../ui/units/WDArmyIcon";

interface UnitControllerProps {
  meta: GameIconProps["meta"];
  type: GameIconProps["type"];
  iconState: GameIconProps["iconState"];
}

const WDUnitController: React.FC<UnitControllerProps> = function ({
  meta,
  type,
  iconState,
}): React.ReactElement {
  const dispatch = useAppDispatch();

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
