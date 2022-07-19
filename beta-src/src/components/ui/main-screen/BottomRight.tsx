import React, { ReactElement, FunctionComponent } from "react";
import Position from "../../../enums/Position";
import Season from "../../../enums/Season";
import WDPositionContainer from "../WDPositionContainer";
import WDPhaseButton from "./buttons/WDPhaseButton";
import WDPhaseSelector from "../phase-selector/WDPhaseSelector";
import { IOrderDataHistorical } from "../../../models/Interfaces";
import { Unit } from "../../../utils/map/getUnits";
import { CountryTableData } from "../../../interfaces";
import OpenModalButton from "./buttons/OpenModalButton";

interface BottomRightProps {
  phaseSelectorOpen: boolean;
  onPhaseSelectorClick: () => void;
  orders: IOrderDataHistorical[];
  units: Unit[];
  allCountries: CountryTableData[];
  userTableData: any;
  currentSeason: Season;
  currentYear: number;
  totalPhases: number;
}

const BottomRight: FunctionComponent<BottomRightProps> = function ({
  phaseSelectorOpen,
  onPhaseSelectorClick,
  orders,
  units,
  allCountries,
  userTableData,
  currentSeason,
  currentYear,
  totalPhases,
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
          <WDPhaseButton
            season={currentSeason}
            text={`${currentSeason.charAt(0)}${currentYear}`}
            onClick={onPhaseSelectorClick}
          />
        </div>
      </WDPositionContainer>
      {phaseSelectorOpen && (
        <WDPhaseSelector
          currentSeason={currentSeason}
          currentYear={currentYear}
          totalPhases={totalPhases}
        />
      )}
    </>
  );
};

export default BottomRight;
