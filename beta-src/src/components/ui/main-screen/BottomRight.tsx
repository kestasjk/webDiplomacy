import React, { ReactElement, FunctionComponent } from "react";
import { useAppSelector } from "../../../state/hooks";
import Position from "../../../enums/Position";
import WDPositionContainer from "../WDPositionContainer";
import RightButton from "./RightButton";
import Season from "../../../enums/Season";
import { gameOverview } from "../../../state/game/game-api-slice";
import WDYearSelector from "../fast-foward-selector/WDYearSelector";
import { IOrderDataHistorical } from "../../../models/Interfaces";
import { Unit } from "../../../utils/map/getUnits";
import { CountryTableData } from "../../../interfaces";
import OpenModalButton from "./OpenModalButton";

interface BottomRightProps {
  phaseSelectorOpen: boolean;
  onPhaseSelectorClick: () => void;
  orders: IOrderDataHistorical[];
  units: Unit[];
  allCountries: CountryTableData[];
  userTableData: any;
}

const BottomLeft: FunctionComponent<BottomRightProps> = function ({
  phaseSelectorOpen,
  onPhaseSelectorClick,
  orders,
  units,
  allCountries,
  userTableData,
}: BottomRightProps): ReactElement {
  return (
    <>
      <WDPositionContainer
        position={Position.BOTTOM_RIGHT}
        bottom={phaseSelectorOpen ? 40 : 4}
      >
        <div>
          <OpenModalButton
            orders={orders}
            units={units}
            allCountries={allCountries}
            userTableData={userTableData}
          />
          <RightButton
            image="phase"
            text="a1901"
            onClick={onPhaseSelectorClick}
          />
        </div>
      </WDPositionContainer>
      {phaseSelectorOpen && (
        <WDYearSelector
          defaultYear={1901}
          defaultSeason={Season.AUTUMN}
          onSelected={(seasonSelected: Season, yearSelected: number) =>
            console.log(seasonSelected, yearSelected)
          }
        />
      )}
    </>
  );
};

export default BottomLeft;
