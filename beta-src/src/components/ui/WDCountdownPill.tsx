import * as React from "react";
import { useEffect, useState } from "react";
import { Avatar, Chip } from "@mui/material";
import BlackCountdownIcon from "../../assets/png/icn_phase_countdown_black.png";
import RedCountdownIcon from "../../assets/png/icn_phase_countdown_red.png";
import calculateAndSortRemainingTime from "../../utils/calculateAndSortRemainingTime";
import { ParsedTimeInterface } from "../../interfaces/ParsedTimeInterface";

interface WDCountdownPillProps {
  remainingTime: number;
}

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({
  remainingTime,
}) {
  const [endTime] = useState<number>(+new Date(remainingTime * 1000));

  const [quarterTimeRemaining] = useState(
    endTime - (endTime - +new Date()) / 4,
  );

  const [timeLeft, setTimeLeft] = useState<ParsedTimeInterface>(
    calculateAndSortRemainingTime(endTime),
  );

  useEffect(() => {
    const timer = setTimeout(() => {
      setTimeLeft(calculateAndSortRemainingTime(endTime));
    }, 1000);

    return () => clearTimeout(timer);
  });

  let chipDisplay: string;
  let chipColor = "black";
  let image = BlackCountdownIcon;

  if (timeLeft.days) {
    chipDisplay = `${timeLeft.days}d`;
    if (timeLeft.hours) {
      chipDisplay = `${chipDisplay} ${timeLeft.hours}h`;
    }
    if (!timeLeft.hours && timeLeft.minutes) {
      chipDisplay = `${chipDisplay} ${timeLeft.minutes}m`;
    }
  } else if (timeLeft.hours) {
    chipDisplay = `${timeLeft.hours}h`;
    if (timeLeft.minutes) {
      chipDisplay = `${chipDisplay} ${timeLeft.minutes}m`;
    }
    if (!timeLeft.minutes && timeLeft.seconds) {
      chipDisplay = `${chipDisplay} ${timeLeft.seconds}s`;
    }
  } else if (timeLeft.minutes) {
    chipDisplay = `${timeLeft.minutes}m ${timeLeft.seconds}s`;
  } else if (timeLeft.seconds) {
    chipDisplay = `${timeLeft.seconds}s`;
  } else {
    chipDisplay = `Time Up`;
  }

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
