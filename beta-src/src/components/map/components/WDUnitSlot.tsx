import * as React from "react";
import { useState } from "react";
import Country from "../../../enums/Country";
import { AbsoluteCoordinates } from "../../../interfaces";
import TerritoryName from "../../../types/TerritoryName";
import WDArmyIcon from "../../svgr-components/WDArmyIcon";
import WDFleetIcon from "../../svgr-components/WDFleetIcon";
import UIState from "../../../enums/UIState";
import debounce from "../../../utils/debounce";

interface WDUnitSlotProps extends AbsoluteCoordinates {
  name: string;
  territoryName: TerritoryName;
}

const WDUnitSlot: React.FC<WDUnitSlotProps> = function ({
  name,
  territoryName,
  x,
  y,
}): React.ReactElement {
  const [iconState, setIconState] = useState<UIState>(UIState.NONE);
  // temporarily adding army or fleet icons to help verify unit positions - TODO
  // const rand = Math.round(Math.random());
  const rand = true;

  const selectedStateHandler = () => {
    if (iconState === UIState.SELECTED) {
      setIconState(UIState.NONE);
      return;
    }
    setIconState(UIState.SELECTED);
  };

  const debounceHandler = debounce(() => {
    selectedStateHandler();
  }, 200);

  const onClickHandler = (e) => {
    debounceHandler[1]();
    debounceHandler[0](e);
  };

  return (
    <svg
      id={`${territoryName}-${name}-unit-slot`}
      data-tname={territoryName}
      x={x}
      y={y}
    >
      {rand && (
        <WDArmyIcon
          viewBox="5 6 35 35"
          country={Country.FRANCE}
          iconState={iconState}
          onClick={onClickHandler}
        />
      )}
      {!rand && (
        <WDFleetIcon
          viewBox="2 7 40 40"
          country={Country.FRANCE}
          iconState={iconState}
          onClick={onClickHandler}
        />
      )}
    </svg>
  );
};

export default WDUnitSlot;
