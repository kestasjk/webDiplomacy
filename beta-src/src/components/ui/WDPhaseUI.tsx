import * as React from "react";
import { gameOverview } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import WDCountdownPill from "./WDCountdownPill";
import WDPillScroller from "./WDPillScroller";
import { abbrMap } from "../../enums/Country";
import Season from "../../enums/Season";
import { IOrderDataHistorical } from "../../models/Interfaces";

const getCurPhaseMinutes = function (phaseMinutes, phaseMinutesRB, phase) {
  if (phaseMinutesRB !== -1 && (phase === "Retreats" || phase === "Builds")) {
    return phaseMinutesRB;
  }
  return phaseMinutes;
};

interface WDPhaseUIProps {
  gamePhase: string;
  gameSeason: Season;
  gameYear: number;
  viewedPhase: string;
  viewedSeason: Season;
  viewedYear: number;
  orders: IOrderDataHistorical[];
  phaseSelectorOpen: boolean;
}

const WDPhaseUI: React.FC<WDPhaseUIProps> = function ({
  gamePhase,
  gameSeason,
  gameYear,
  viewedPhase,
  viewedSeason,
  viewedYear,
  orders,
  phaseSelectorOpen,
}: WDPhaseUIProps): React.ReactElement {
  const {
    phaseMinutes,
    phaseMinutesRB,
    processTime,
    phase,
    processStatus,
    pauseTimeRemaining,
    user,
  } = useAppSelector(gameOverview);

  const phaseSeconds =
    getCurPhaseMinutes(phaseMinutes, phaseMinutesRB, phase) * 60;

  const gameIsFinished = gamePhase === "Finished";
  const seconds = Math.floor(new Date().getTime() / 1000);

  return (
    <div className="flex flex-col space-y-2">
      {user && (
        <div
          className={`py-1 px-2 rounded-md w-fit font-medium select-none text-white bg-${user.member.country.toLocaleLowerCase()}-main`}
        >
          {/* title={`Currently playing as ${user.member.country}`} */}
          {abbrMap[user.member.country]}
        </div>
      )}
      <WDPillScroller
        orderStatus={user?.member.orderStatus}
        country={user?.member.country || ""}
        viewedPhase={viewedPhase}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
        orders={orders}
        phaseSelectorOpen={phaseSelectorOpen}
      />
      {(processTime || pauseTimeRemaining) && !gameIsFinished && (
        <WDCountdownPill
          endTime={processTime || (pauseTimeRemaining || 0) + seconds}
          phaseTime={phaseSeconds}
          viewedPhase={viewedPhase}
          viewedSeason={viewedSeason}
          viewedYear={viewedYear}
          gamePhase={gamePhase}
          gameSeason={gameSeason}
          gameYear={gameYear}
          isPaused={processStatus === "Paused" || false}
        />
      )}
    </div>
  );
};

export default WDPhaseUI;
