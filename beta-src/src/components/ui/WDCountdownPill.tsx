import * as React from "react";
import { useEffect, useState } from "react";
import TimeNotRunningOutIcon from "../../assets/png/icn_phase_countdown_black.png";
import TimeRunningOutIcon from "../../assets/png/icn_phase_countdown_red.png";
import Season from "../../enums/Season";
import formatTime, { getFormattedTimeLeft } from "../../utils/formatTime";
import { formatPSYForDisplay } from "../../utils/formatPhaseForDisplay";
import useViewport from "../../hooks/useViewport";

interface WDCountdownPillProps {
  endTime: number;
  phaseTime: number;
  viewedPhase: string;
  viewedSeason: Season;
  viewedYear: number;
  gamePhase: string;
  gameSeason: Season;
  gameYear: number;
  isPaused: boolean;
}

const milli = 1000;

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({
  endTime,
  phaseTime,
  viewedPhase,
  viewedSeason,
  viewedYear,
  gamePhase,
  gameSeason,
  gameYear,
  isPaused,
}) {
  const endTimeInMilliSeconds = endTime * milli;
  const phaseTimeInMilliSeconds = phaseTime * milli;
  const [viewport] = useViewport();
  const quarterTimeRemaining =
    endTimeInMilliSeconds - phaseTimeInMilliSeconds / 4;

  const [formattedTimeLeft, setFormattedTimeLeft] = useState<string>(
    getFormattedTimeLeft(endTime),
  );

  const [chipDisplay, setChipDisplay] = useState<string>();

  useEffect(() => {
    let timer;
    if (!isPaused) {
      timer = setInterval(() => {
        const newFormattedTimeLeft = getFormattedTimeLeft(endTime);
        setFormattedTimeLeft(newFormattedTimeLeft);
      }, milli);
    }

    return () => clearInterval(timer);
  }, [endTime, setFormattedTimeLeft]);

  const isTimeRunningOut = +new Date() > quarterTimeRemaining;
  const shouldDisplayGamePhase =
    viewedPhase !== gamePhase ||
    viewedSeason !== gameSeason ||
    viewedYear !== gameYear;

  useEffect(() => {
    let cd = formattedTimeLeft;
    if (viewport.width >= 600) {
      if (shouldDisplayGamePhase) {
        cd += ` for ${formatPSYForDisplay({
          phase: gamePhase,
          season: gameSeason,
          year: gameYear,
        })}
      `;
      } else {
        cd += " this phase";
      }
    }

    if (isPaused) {
      cd = `PAUSED (${cd})`;
    }

    setChipDisplay(cd);
  }, [formattedTimeLeft, setChipDisplay]);

  return (
    <div
      className={`flex items-center py-1 pl-1 pr-3 rounded-full text-white ${
        // eslint-disable-next-line no-nested-ternary
        isTimeRunningOut
          ? "bg-red-600"
          : isPaused
          ? "bg-yellow-300 text-black"
          : "bg-black"
      }`}
    >
      <div>
        <img
          className="h-6 mr-1"
          src={isTimeRunningOut ? TimeRunningOutIcon : TimeNotRunningOutIcon}
          alt="Countdown icon"
        />
      </div>
      {chipDisplay}
    </div>
  );
};

export default WDCountdownPill;
