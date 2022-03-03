import * as React from "react";
import { useEffect, useState } from "react";
import { Avatar, Chip } from "@mui/material";
import BlackCountdownIcon from "../../assets/png/icn_phase_countdown_black.png";
import RedCountdownIcon from "../../assets/png/icn_phase_countdown_red.png";
import calculateAndSortRemainingTime from "../../utils/calculateAndSortRemainingTime";
import { ParsedTime } from "../../interfaces/ParsedTime";
import formatTime from "../../utils/formatTime";

interface WDCountdownPillProps {
  endTime: number;
}

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({ endTime }) {
  const endTimeInMilliSeconds = endTime * 1000;

  const [quarterTimeRemaining] = useState(
    endTimeInMilliSeconds - (endTimeInMilliSeconds - +new Date()) / 4,
  );

  const [timeLeft, setTimeLeft] = useState<ParsedTime>(
    calculateAndSortRemainingTime(endTime),
  );

  useEffect(() => {
    const timer = setTimeout(() => {
      setTimeLeft(calculateAndSortRemainingTime(endTime));
    }, 1000);

    return () => clearTimeout(timer);
  });

  const chipDisplay: string = formatTime(timeLeft);
  let chipColor = "black";
  let image = BlackCountdownIcon;

  if (+new Date() > quarterTimeRemaining) {
    chipColor = "red";
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
        backgroundColor: `${chipColor}`,
        color: "white",
      }}
      label={chipDisplay}
    />
  );
};

export default WDCountdownPill;
