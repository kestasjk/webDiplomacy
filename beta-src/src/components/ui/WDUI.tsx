import React, { useState, FunctionComponent, ReactElement } from "react";
import { useTheme } from "@mui/material";

import { IOrderDataHistorical } from "../../models/Interfaces";
import WDGameFinishedOverlay from "./WDGameFinishedOverlay";
import { Unit } from "../../utils/map/getUnits";
import { TopLeft, TopRight, BottomLeft, BottomRight } from "./main-screen";
import { useAppSelector } from "../../state/hooks";
import { gameOverview } from "../../state/game/game-api-slice";
import { CountryTableData } from "../../interfaces";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Country, { abbrMap } from "../../enums/Country";

interface WDUIProps {
  orders: IOrderDataHistorical[];
  units: Unit[];
  viewingGameFinishedPhase: boolean;
}

const WDUI: FunctionComponent<WDUIProps> = function ({
  orders,
  units,
  viewingGameFinishedPhase,
}): ReactElement {
  const theme = useTheme();
  const { phase, user, members } = useAppSelector(gameOverview);

  const [phaseSelectorOpen, setPhaseSelectorOpen] = useState<boolean>(false);
  const gameIsFinished = phase === "Finished";

  const allCountries: CountryTableData[] = [];

  const getCountrySortIdx = function (countryID: number) {
    // Sort user country to the front
    if (countryID === user?.member.countryID) return -1;
    return countryID;
  };

  const constructTableData = (member) => {
    const memberCountry: Country = countryMap[member.country];
    return {
      ...member,
      abbr: abbrMap[member.country],
      color: theme.palette[memberCountry].main,
      power: memberCountry,
      votes: member.votes,
    };
  };

  members.forEach((member) => {
    allCountries.push(constructTableData(member));
  });
  allCountries.sort(
    (x, y) => getCountrySortIdx(x.countryID) - getCountrySortIdx(y.countryID),
  );

  const userTableData = user ? constructTableData(user.member) : null;

  return (
    <>
      <TopLeft />
      {!gameIsFinished && <BottomLeft phaseSelectorOpen={phaseSelectorOpen} />}
      <TopRight
        orders={orders}
        units={units}
        allCountries={allCountries}
        userTableData={userTableData}
      />
      <BottomRight
        phaseSelectorOpen={phaseSelectorOpen}
        onPhaseSelectorClick={() => setPhaseSelectorOpen(!phaseSelectorOpen)}
      />
      {/* TODO: do not delete this yet */}
      <div className="hidden bottom-4 bottom-40" />
      {gameIsFinished && viewingGameFinishedPhase && (
        <WDGameFinishedOverlay allCountries={allCountries} />
      )}
    </>
  );
};

export default WDUI;
