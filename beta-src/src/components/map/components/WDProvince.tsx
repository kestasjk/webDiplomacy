import { useTheme } from "@mui/material";
import * as React from "react";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import TerritoryMap, {
  territoryToWebdipName,
} from "../../../data/map/variants/classic/TerritoryMap";
import Territories from "../../../data/Territories";
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
import Territory from "../../../enums/map/variants/classic/Territory";
import OrdersMeta from "../../../state/interfaces/SavedOrders";

interface WDProvinceProps {
  provinceMapData: ProvinceMapData;
  territoryMeta: TerritoryMeta | undefined;
  units: Unit[];
}

const WDTerritory: React.FC<WDProvinceProps> = function ({
  provinceMapData,
  territoryMeta,
  units,
}): React.ReactElement {
  const theme = useTheme();
  const dispatch = useAppDispatch();

  const { user, members } = useAppSelector(gameOverview);
  const userCountry = countryMap[user.member.country];

  const { territory } = provinceMapData;
  let territoryFill = "none";
  let territoryFillOpacity = 0;
  const territoryStrokeOpacity = 1;
  if (territoryMeta?.countryID) {
    const ownerCountryID = territoryMeta?.countryID;
    const ownerCountry =
      members.find(({ countryID }) => String(countryID) === ownerCountryID)
        ?.country || "null";
    territoryFill = theme.palette[ownerCountry]?.main;
    territoryFillOpacity = 0.4;
  }
  const curOrder = useAppSelector(gameOrder);

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
    .filter(
      (unit) =>
        (unit.mappedTerritory.parent || unit.mappedTerritory.territory) ===
        territoryMeta?.territory,
    )
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

      if (curOrder.unitID === unit.unit.id && curOrder.type) {
        territoryFillOpacity = 0.9;
        territoryFill = theme.palette[userCountry]?.main;
      }
      if (curOrder.fromTerrID === territoryMeta?.id) {
        territoryFillOpacity = 0.7;
        // yuck
        const ownerCountry = members.find(
          (m) => m.countryID === Number(territoryMeta.ownerCountryID),
        )?.country;
        territoryFill = theme.palette[ownerCountry || ""]?.main;
      }
      const wdUnit = (
        <WDUnit
          id={`${territory}-unit`}
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

  const clickAction = function (
    evt: React.MouseEvent<SVGGElement, MouseEvent>,
    clickObject: ClickObjectType,
  ) {
    dispatch(
      gameApiSliceActions.processMapClick({
        evt,
        territory,
      }),
    );
  };
  return (
    <svg
      height={provinceMapData.height}
      id={`${territory}-territory`}
      viewBox={provinceMapData.viewBox}
      width={provinceMapData.width}
      x={provinceMapData.x}
      y={provinceMapData.y}
    >
      <g onClick={(e) => clickAction(e, "territory")}>
        {provinceMapData.texture?.texture && (
          <path
            d={provinceMapData.path}
            fill={provinceMapData.texture.texture}
            id={`${territory}-texture`}
            stroke={provinceMapData.texture.stroke}
            strokeOpacity={provinceMapData.texture.strokeOpacity}
            strokeWidth={provinceMapData.texture.strokeWidth}
          />
        )}
        <path
          d={provinceMapData.path}
          fill={territoryFill}
          fillOpacity={territoryFillOpacity}
          id={`${territory}-control-path`}
          stroke={theme.palette.primary.main}
          strokeOpacity={1}
          strokeWidth={territoryStrokeOpacity}
        />
      </g>
      {provinceMapData.centerPos && (
        <g className="no-pointer-events">
          <WDCenter
            territory={territory}
            x={provinceMapData.centerPos.x}
            y={provinceMapData.centerPos.y}
          />
        </g>
      )}
      {provinceMapData.labels &&
        provinceMapData.labels.map(({ name, text, style, x, y }, i) => {
          let txt = text;
          const id = `${territory}-label-${name}`;
          if (!txt) {
            txt = provinceMapData.abbr;
          }
          return (
            <g key={id} className="no-pointer-events">
              <WDLabel
                id={id}
                name={name}
                key={id || i}
                style={style}
                text={txt}
                x={x}
                y={y}
              />
            </g>
          );
        })}
      {provinceMapData.unitSlots
        .filter(({ name }) => name in unitFCs)
        .map(({ name, x, y }) => (
          <WDUnitSlot key={name} name={name} territory={territory} x={x} y={y}>
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
              territory={territory}
              x={arrowReceiver.x - UNIT_WIDTH / 2}
              y={arrowReceiver.y - UNIT_HEIGHT / 2}
            >
              {unitFCsDislodging[name]}
            </WDUnitSlot>
          );
        })}
    </svg>
  );
};

export default WDTerritory;
