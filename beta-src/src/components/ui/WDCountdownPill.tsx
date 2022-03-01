import * as React from "react";
import Chip from "@mui/material/Chip";

interface WDCountdownPillProps {
  remainingTime: any;
}

const WDCountdownPill: React.FC<WDCountdownPillProps> = function ({
  remainingTime,
}) {
  const [endTime, setEndLeft] = React.useState(
    new Date().getTime() +
      remainingTime.seconds * 1000 +
      remainingTime.minutes * 60000 +
      remainingTime.hours * 3600000 +
      remainingTime.days * 86400000,
  );

  const [quarterTimeRemaining, setQuarterTimeRemaining] = React.useState(
    +new Date(endTime) - (+new Date(endTime) - +new Date()) / 4,
  );

  function calculateTimeLeft() {
    const difference = +new Date(endTime) - +new Date();

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
  }

  return (
    <Chip
      sx={{
        backgroundColor: `${chipColor}`,
        color: "white",
      }}
      label={chipDisplay}
    />
  );
};

export default WDCountdownPill;
