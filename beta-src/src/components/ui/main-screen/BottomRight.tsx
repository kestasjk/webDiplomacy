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
import { useAppSelector } from "../../../state/hooks";
import { gameOverview } from "../../../state/game/game-api-slice";

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
  viewedPhase: string;
  onClickOutside: () => void;
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
  viewedPhase,
  onClickOutside,
}: BottomRightProps): ReactElement {
  const { phase } = useAppSelector(gameOverview);

  // eslint-disable-next-line react/jsx-no-useless-fragment
  if (phase === "Pre-game") return <></>;

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
            viewedPhase={viewedPhase}
          />
        </div>
      </WDPositionContainer>
      {phaseSelectorOpen && (
        <WDPhaseSelector
          currentSeason={currentSeason}
          currentYear={currentYear}
          totalPhases={totalPhases}
          onClickOutside={onClickOutside}
        />
      )}
    </>
  );
};

export default BottomRight;
