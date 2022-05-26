import * as React from "react";
import { useEffect, useState } from "react";
import { Avatar, Chip, useTheme } from "@mui/material";
import TimeNotRunningOutIcon from "../../assets/png/icn_phase_countdown_black.png";
import TimeRunningOutIcon from "../../assets/png/icn_phase_countdown_red.png";
import Season from "../../enums/Season";
import parseSeconds from "../../utils/parseSeconds";
import { ParsedTime } from "../../interfaces/ParsedTime";
import formatTime from "../../utils/formatTime";
import formatPhaseForDisplay from "../../utils/formatPhaseForDisplay";

interface WDCountdownPillProps {
  endTime: number;
  phaseTime: number;
  viewedPhase: string;
  viewedSeason: Season;
  viewedYear: number;
  gamePhase: string;
  gameSeason: Season;
  gameYear: number;
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
}) {
  const theme = useTheme();
  const endTimeInMilliSeconds = endTime * milli;
  const phaseTimeInMilliSeconds = phaseTime * milli;

  const quarterTimeRemaining =
    endTimeInMilliSeconds - phaseTimeInMilliSeconds / 4;

  const secondsLeft = endTime - +new Date() / milli;

  const [timeLeft, setTimeLeft] = useState<ParsedTime>(
    parseSeconds(secondsLeft),
  );

  useEffect(() => {
    const timer = setTimeout(() => {
      setTimeLeft(parseSeconds(secondsLeft));
    }, 1000);

    return () => clearTimeout(timer);
  }, [secondsLeft]);

  const isTimeRunningOut = +new Date() > quarterTimeRemaining;
  const shouldDisplayGamePhase =
    viewedPhase !== gamePhase ||
    viewedSeason !== gameSeason ||
    viewedYear !== gameYear;
  let chipDisplay: string = formatTime(timeLeft);
  if (shouldDisplayGamePhase) {
    chipDisplay += ` for ${gameSeason} ${gameYear} ${formatPhaseForDisplay(
      gamePhase,
    )}`;
  } else {
    chipDisplay += " this phase";
  }

  return (
    <Chip
      avatar={
        <Avatar>
          <img
            src={isTimeRunningOut ? TimeRunningOutIcon : TimeNotRunningOutIcon}
            alt="Countdown icon"
          />
        </Avatar>
      }
      sx={{
        backgroundColor: isTimeRunningOut
          ? theme.palette.error.main
          : theme.palette.primary.main,
        color: theme.palette.secondary.main,
        filter: theme.palette.svg.filters.dropShadows[0],
      }}
      label={chipDisplay}
    />
  );
};

export default WDCountdownPill;
