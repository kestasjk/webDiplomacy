import * as React from "react";
import { GameIconProps } from "../../interfaces/Icons";
import debounce from "../../utils/debounce";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import { gameApiSliceActions } from "../../state/game/game-api-slice";
import WDFleetIcon, {
  FLEET_RAW_ICON_WIDTH,
  FLEET_RAW_ICON_HEIGHT,
} from "../ui/units/WDFleetIcon";
import WDArmyIcon, {
  ARMY_RAW_ICON_WIDTH,
  ARMY_RAW_ICON_HEIGHT,
} from "../ui/units/WDArmyIcon";

interface UnitControllerProps {
  meta: GameIconProps["meta"];
  type: GameIconProps["type"];
  iconState: GameIconProps["iconState"];
  unitWidth: number;
  unitHeight: number;
}

const WDUnitController: React.FC<UnitControllerProps> = function ({
  meta,
  type,
  iconState,
  unitWidth,
  unitHeight,
}): React.ReactElement {
  const iconWidth =
    type === "Fleet" ? FLEET_RAW_ICON_WIDTH : ARMY_RAW_ICON_WIDTH;
  const iconHeight =
    type === "Fleet" ? FLEET_RAW_ICON_HEIGHT : ARMY_RAW_ICON_HEIGHT;

  // The icons expect that 0,0 is the upper left corner of the unit, whereas this WDUnitController
  // svg element is expected to be positioned such that 0,0 is the center of the
  // unit. So we need to translate to reconcile these.
  const xOffset = iconWidth / 2;
  const yOffset = iconHeight / 2;

  // Translate from the coordinate scale of the icons in WDFleetIcon and
  // WDArmyIcon to the size we want to actually draw this unit
  const xScale = unitWidth / iconWidth;
  const yScale = unitHeight / iconHeight;

  const transform = `scale(${xScale}, ${yScale}) translate(-${xOffset},-${yOffset})`;
  return (
    <g
      style={{ pointerEvents: "none", overflow: "visible" }}
      transform={transform}
    >
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
