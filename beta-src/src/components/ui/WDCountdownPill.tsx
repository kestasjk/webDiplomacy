import * as React from "react";
import { useEffect, useState } from "react";
import { Avatar, Chip, useTheme } from "@mui/material";
import BlackCountdownIcon from "../../assets/png/icn_phase_countdown_black.png";
import RedCountdownIcon from "../../assets/png/icn_phase_countdown_red.png";
import parseSeconds from "../../utils/parseSeconds";
import { ParsedTime } from "../../interfaces/ParsedTime";
import formatTime from "../../utils/formatTime";

interface WDCountdownPillProps {
  endTime: number;
  phaseTime: number;
}

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({
  endTime,
  phaseTime,
}) {
  const theme = useTheme();
  const endTimeInMilliSeconds = endTime * 1000;
  const phaseTimeInSeconds = phaseTime * 1000;

  const quarterTimeRemaining = endTimeInMilliSeconds - phaseTimeInSeconds / 4;

  const secondsLeft = endTime - +new Date() / 1000;

  const [timeLeft, setTimeLeft] = useState<ParsedTime>(
    parseSeconds(secondsLeft),
  );

  useEffect(() => {
    const timer = setTimeout(() => {
      setTimeLeft(parseSeconds(secondsLeft));
    }, 1000);

    return () => clearTimeout(timer);
  });

  const chipDisplay: string = formatTime(timeLeft);
  let chipColor = theme.palette.primary.main;
  let image = BlackCountdownIcon;

  if (+new Date() > quarterTimeRemaining) {
    chipColor = theme.palette.error.main;
    image = RedCountdownIcon;
  }

  return (
    <Chip
      avatar={
        <Avatar>
          <img src={image} alt="" />
        </Avatar>
      }
      sx={{
        backgroundColor: chipColor,
        color: theme.palette.secondary.main,
      }}
      label={chipDisplay}
    />
  );
};

export default WDCountdownPill;
