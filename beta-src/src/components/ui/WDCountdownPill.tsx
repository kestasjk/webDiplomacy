import * as React from "react";
import { Avatar, Chip } from "@mui/material";
import BlackCountdownIcon from "../../assets/png/icn_phase_countdown_black.png";
import RedCountdownIcon from "../../assets/png/icn_phase_countdown_red.png";

interface WDCountdownPillProps {
  remainingTime: number;
}

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({
  remainingTime,
}) {
  const [endTime, setEndLeft] = React.useState(+new Date(remainingTime * 1000));

  const [quarterTimeRemaining, setQuarterTimeRemaining] = React.useState(
    endTime - (endTime - +new Date()) / 4,
  );

  function calculateTimeLeft() {
    const difference = endTime - +new Date();

    let timeLeft = {};

    if (difference > 0) {
      timeLeft = {
        days: Math.floor(difference / (1000 * 60 * 60 * 24)),
        hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
        minutes: Math.floor((difference / 1000 / 60) % 60),
        seconds: Math.floor((difference / 1000) % 60),
      };
    }

    return timeLeft;
  }
  const [timeLeft, setTimeLeft] = React.useState<any>(calculateTimeLeft());

  React.useEffect(() => {
    const timer = setTimeout(() => {
      setTimeLeft(calculateTimeLeft());
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
