import * as React from "react";
import { useTheme } from "@mui/material/styles";
import { GameIconProps } from "../../../interfaces/Icons";
import WDFleetIcon from "./WDFleetIcon";
import WDUnitController from "../../controllers/WDUnitController";
import UIState from "../../../enums/UIState";
import { useAppSelector } from "../../../state/hooks";
import { gameUnitState } from "../../../state/game/game-api-slice";

const WDFleet: React.FC<GameIconProps> = function ({
  country,
  height = 50,
  iconState = UIState.NONE,
  id = undefined,
  meta,
  viewBox,
  width = 50,
}): React.ReactElement {
  const theme = useTheme();

  // FIXME: dedup with WDArmy
  const unitState = useAppSelector(gameUnitState);
  const thisUnitState = unitState[meta.unit.id];

  return (
    <svg
      filter={theme.palette.svg.filters.dropShadows[1]}
      height={height}
      id={id}
      viewBox={viewBox}
      width={width}
    >
      <WDUnitController meta={meta}>
        <WDFleetIcon country={country} iconState={thisUnitState} />
      </WDUnitController>
    </svg>
  );
};

export default WDFleet;
