import React, { useState, FunctionComponent, ReactElement } from "react";
import { useTheme } from "@mui/material";

import { IOrderDataHistorical } from "../../models/Interfaces";
import WDGameFinishedOverlay from "./WDGameFinishedOverlay";
import { Unit } from "../../utils/map/getUnits";
import {
  TopLeft,
  TopRight,
  BottomLeft,
  BottomRight,
  BottomMiddle,
} from "./main-screen";
import { useAppSelector } from "../../state/hooks";
import {
  gameOverview,
  gameStatus,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import { CountryTableData } from "../../interfaces";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Country, { abbrMap } from "../../enums/Country";
import {
  getGamePhaseSeasonYear,
  getHistoricalPhaseSeasonYear,
} from "../../utils/state/getPhaseSeasonYear";
import WDLoading from "../miscellaneous/Loading";

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
  const { phase, season, year, user, members } = useAppSelector(gameOverview);

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
      color: theme.palette[memberCountry]?.main,
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

  const gameStatusData = useAppSelector(gameStatus);

  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);

  const {
    phase: gamePhase,
    season: gameSeason,
    year: gameYear,
  } = getGamePhaseSeasonYear(phase, season, year);

  let {
    phase: viewedPhase,
    season: viewedSeason,
    year: viewedYear,
  } = getHistoricalPhaseSeasonYear(gameStatusData, viewedPhaseIdx);

  // On the very last phase of a finished game, webdip API might give an
  // entirely erroneous year/season/phase. So instead, trust the one in the
  // overview.
  if (viewedPhaseIdx === gameStatusData.phases.length - 1) {
    viewedPhase = gamePhase;
    viewedSeason = gameSeason;
    viewedYear = gameYear;
  }

  return (
    <>
      <WDLoading percentage={80} />
      <TopLeft
        gamePhase={gamePhase}
        gameSeason={gameSeason}
        gameYear={gameYear}
        viewedPhase={viewedPhase}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
        orders={orders}
        phaseSelectorOpen={phaseSelectorOpen}
      />
      <TopRight />
      {!gameIsFinished && <BottomLeft phaseSelectorOpen={phaseSelectorOpen} />}
      <BottomRight
        phaseSelectorOpen={phaseSelectorOpen}
        onPhaseSelectorClick={() => setPhaseSelectorOpen(!phaseSelectorOpen)}
        orders={orders}
        units={units}
        allCountries={allCountries}
        userTableData={userTableData}
        currentSeason={viewedSeason}
        currentYear={viewedYear}
        totalPhases={gameStatusData.phases.length}
        onClickOutside={() => setPhaseSelectorOpen(false)}
      />
      <BottomMiddle
        phaseSelectorOpen={phaseSelectorOpen}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
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
