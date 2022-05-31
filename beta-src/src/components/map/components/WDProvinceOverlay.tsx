import { useTheme } from "@mui/material";
import * as React from "react";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import TerritoryMap, {
  territoryToWebdipName,
} from "../../../data/map/variants/classic/TerritoryMap";
import UIState from "../../../enums/UIState";
import { Coordinates, ProvinceMapData } from "../../../interfaces";
import {
  gameApiSliceActions,
  gameMaps,
  gameOrder,
  gameOrdersMeta,
  gameOverview,
  gameTerritoriesMeta,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import { TerritoryMeta } from "../../../state/interfaces/TerritoriesState";
import ClickObjectType from "../../../types/state/ClickObjectType";
import OrderType from "../../../types/state/OrderType";
import UnitType from "../../../types/UnitType";
import WDUnit, { UNIT_HEIGHT, UNIT_WIDTH } from "../../ui/units/WDUnit";
import WDCenter from "./WDCenter";
import WDLabel from "./WDLabel";
import WDUnitSlot from "./WDUnitSlot";
import { Unit, UnitDrawMode } from "../../../utils/map/getUnits";
import Province from "../../../enums/map/variants/classic/Province";
import Territory from "../../../enums/map/variants/classic/Territory";
import OrdersMeta from "../../../state/interfaces/SavedOrders";
import { IProvinceStatus } from "../../../models/Interfaces";

interface WDProvinceOverlayProps {
  provinceMapData: ProvinceMapData;
  units: Unit[];
  highlightChoice: boolean;
}

const WDProvinceOverlay: React.FC<WDProvinceOverlayProps> = function ({
  provinceMapData,
  units,
  highlightChoice,
}): React.ReactElement {
  const { province } = provinceMapData;

  // Maps unitSlot name -> unit to draw.
  const unitFCs: { [key: string]: React.ReactElement } = {};
  // Maps unitSlot name -> unit to draw, but specifically for units
  // that are currently disloging another unit on a retreat phase.
  // This is separate because we need to draw the
  // dislodger unit in an alternative location when there are two
  // units in a territory so that they don't overlap each other, including
  // when those units share the same unitSlot within that territory.
  const unitFCsDislodging: { [key: string]: React.ReactElement } = {};

  units
    .filter((unit) => unit.mappedTerritory.province === province)
    .forEach((unit) => {
      let unitState: UIState;
      switch (unit.drawMode) {
        case UnitDrawMode.NONE:
          unitState = UIState.NONE;
          break;
        case UnitDrawMode.HOLD:
          unitState = UIState.HOLD;
          break;
        case UnitDrawMode.BUILD:
          unitState = UIState.BUILD;
          break;
        case UnitDrawMode.DISLODGING:
          unitState = UIState.NONE;
          break;
        case UnitDrawMode.DISLODGED:
          unitState = UIState.DISLODGED;
          break;
        case UnitDrawMode.DISBANDED:
          unitState = UIState.DISBANDED;
          break;
        default:
          unitState = UIState.NONE;
          break;
      }
      const wdUnit = (
        <WDUnit
          id={`${province}-unit`}
          country={unit.country}
          meta={unit}
          type={unit.unit.type as UnitType}
          iconState={unitState}
        />
      );
      if (unit.drawMode === UnitDrawMode.DISLODGING) {
        unitFCsDislodging[unit.mappedTerritory.unitSlotName] = wdUnit;
      } else {
        unitFCs[unit.mappedTerritory.unitSlotName] = wdUnit;
      }
    });

  return (
    <svg
      height={provinceMapData.height}
      id={`${province}-province-overlay`}
      viewBox={provinceMapData.viewBox}
      width={provinceMapData.width}
      x={provinceMapData.x}
      y={provinceMapData.y}
      overflow="visible"
    >
      {provinceMapData.unitSlots
        .filter(({ name }) => name in unitFCs)
        .map(({ name, x, y }) => (
          <WDUnitSlot key={name} name={name} x={x} y={y}>
            {unitFCs[name]}
          </WDUnitSlot>
        ))}
      {provinceMapData.unitSlots
        .filter(({ name }) => name in unitFCsDislodging)
        .map(({ name, arrowReceiver }) => {
          const unitName = `${name}-dislodging`;
          // For dislodger units, we draw them at the location of the
          // arrow receiver.
          return (
            <WDUnitSlot
              key={unitName}
              name={unitName}
              x={arrowReceiver.x - UNIT_WIDTH / 2}
              y={arrowReceiver.y - UNIT_HEIGHT / 2}
            >
              {unitFCsDislodging[name]}
            </WDUnitSlot>
          );
        })}
      {highlightChoice && (
        <path
          d={provinceMapData.path}
          fill="none"
          fillOpacity={0.0}
          id={`${province}-choice-outline`}
          stroke="black"
          strokeOpacity={1}
          strokeWidth={5}
        />
      )}
    </svg>
  );
};

export default WDProvinceOverlay;
