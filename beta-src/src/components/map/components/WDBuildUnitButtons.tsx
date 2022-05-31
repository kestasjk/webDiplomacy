/* eslint-disable no-bitwise */
import * as React from "react";
import provincesMapData from "../../../data/map/ProvincesMapData";
import BuildUnit from "../../../enums/BuildUnit";
import Country from "../../../enums/Country";
import Province from "../../../enums/map/variants/classic/Province";
import UIState from "../../../enums/UIState";
import WDArmyIcon from "../../ui/units/WDArmyIcon";
import WDFleetIcon from "../../ui/units/WDFleetIcon";
import { UNIT_WIDTH, UNIT_HEIGHT } from "../../ui/units/WDUnit";

export interface BuildData {
  availableOrder: string;
  clickCallback: (
    availableOrder: BuildData["availableOrder"],
    canBuild: BuildData["canBuild"],
    toTerrID: BuildData["toTerrID"],
  ) => void;
  country: Country;
  canBuild: BuildUnit;
  province: Province;
  unitSlotName: string;
  toTerrID: string;
}

const WDBuildUnitButtons: React.FC<BuildData> = function ({
  availableOrder,
  clickCallback,
  country,
  canBuild,
  province,
  unitSlotName,
  toTerrID,
}): React.ReactElement {
  const provinceMapData = provincesMapData[province];
  let svgX =
    provinceMapData.x +
    provinceMapData.unitSlotsBySlotName[unitSlotName].x +
    UNIT_WIDTH / 2;
  let svgY =
    provinceMapData.y +
    provinceMapData.unitSlotsBySlotName[unitSlotName].y +
    UNIT_HEIGHT / 2;

  let rw = 70;
  const rh = 70;
  const rBorder = 10;
  const rFill = "rgba(0,0,0,.7)";
  const buildButtons: React.ReactElement[] = [];
  const iconStyle: React.CSSProperties = {
    width: 50,
    height: 50,
  };
  const groupStyle: React.CSSProperties = {
    cursor: "pointer",
  };

  const cx = 0 + rw / 2;
  const cy = 0 + rh / 2;
  const r = 25;
  const iconX = cx - r;
  const iconY = cy - r;
  svgY -= rh;
  if (canBuild & BuildUnit.Army) {
    buildButtons.push(
      <g
        key="Army"
        style={groupStyle}
        onClick={() => {
          clickCallback(availableOrder, BuildUnit.Army, toTerrID);
        }}
      >
        <circle fill="white" r={r} cx={cx} cy={cy} />
        <svg x={iconX} y={iconY} style={iconStyle}>
          <WDArmyIcon country={country} iconState={UIState.BUILD} />
        </svg>
      </g>,
    );
  }
  if (canBuild & BuildUnit.Fleet) {
    let shift = 0;
    if (buildButtons.length) {
      shift = rw - rBorder;
      rw = rw * 2 - rBorder;
    }
    buildButtons.push(
      <g
        key="Fleet"
        style={groupStyle}
        onClick={() => {
          clickCallback(availableOrder, BuildUnit.Fleet, toTerrID);
        }}
      >
        <circle fill="white" r={r} cx={cx + shift} cy={cy} />
        <svg x={iconX + shift} y={iconY} style={iconStyle}>
          <WDFleetIcon country={country} iconState={UIState.BUILD} />
        </svg>
      </g>,
    );
  }
  svgX -= rw / 2;
  return (
    <svg x={svgX} y={svgY}>
      <rect
        x={0}
        y={0}
        fill={rFill}
        width={rw}
        height={rh}
        rx={rBorder}
        ry={rBorder}
      />
      {buildButtons}
    </svg>
  );
};

export default WDBuildUnitButtons;
