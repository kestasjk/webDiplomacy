import * as React from "react";
import { useEffect, useState } from "react";
import { Avatar, Chip, useTheme } from "@mui/material";
import TimeNotRunningOutIcon from "../../assets/png/icn_phase_countdown_black.png";
import TimeRunningOutIcon from "../../assets/png/icn_phase_countdown_red.png";
import parseSeconds from "../../utils/parseSeconds";
import { ParsedTime } from "../../interfaces/ParsedTime";
import formatTime from "../../utils/formatTime";

interface WDCountdownPillProps {
  endTime: number;
  phaseTime: number;
}

const milli = 1000;

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({
  endTime,
  phaseTime,
}) {
  const theme = useTheme();
  const endTimeInMilliSeconds = endTime * milli;
  const phaseTimeInSeconds = phaseTime * milli;

  const quarterTimeRemaining = endTimeInMilliSeconds - phaseTimeInSeconds / 4;

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

  const chipDisplay: string = formatTime(timeLeft);
  const isTimeRunningOut = +new Date() > quarterTimeRemaining;

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
      }}
      label={chipDisplay}
    />
  );
};

export default WDCountdownPill;
