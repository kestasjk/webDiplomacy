import { ParsedTime } from "../interfaces/ParsedTime";

export default function formatTime(time: ParsedTime) {
  let chipDisplay: string;
  if (time.days) {
    chipDisplay = `${time.days}d`;
    if (time.hours) {
      chipDisplay = `${chipDisplay} ${time.hours}h`;
    }
    if (!time.hours && time.minutes) {
      chipDisplay = `${chipDisplay} ${time.minutes}m`;
    }
  } else if (time.hours) {
    chipDisplay = `${time.hours}h`;
    if (time.minutes) {
      chipDisplay = `${chipDisplay} ${time.minutes}m`;
    }
    if (!time.minutes && time.seconds) {
      chipDisplay = `${chipDisplay} ${time.seconds}s`;
    }
  } else if (time.minutes) {
    chipDisplay = `${time.minutes}m ${time.seconds}s`;
  } else if (time.seconds) {
    chipDisplay = `${time.seconds}s`;
  } else {
    chipDisplay = `Time Up`;
  }

  return chipDisplay;
}
