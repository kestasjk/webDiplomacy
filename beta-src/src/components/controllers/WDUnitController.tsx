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
  return (
    <g>
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
