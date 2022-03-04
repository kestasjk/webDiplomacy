import { ParsedTime } from "../interfaces/ParsedTime";

export default function formatTime(time: ParsedTime) {
  let chipDisplay: string;
  const availableTimeIntervals = Object.keys(time).filter((key) => time[key]);
  const timeIntervalAbbreviations = availableTimeIntervals.map(
    (interval: string) => {
      return interval.charAt(0);
    },
  );

  if (availableTimeIntervals.length > 1) {
    chipDisplay = `${time[availableTimeIntervals[0]]}${
      timeIntervalAbbreviations[0]
    } ${time[availableTimeIntervals[1]]}${timeIntervalAbbreviations[1]}`;
  } else if (!availableTimeIntervals.length) {
    chipDisplay = "Time Up";
  } else {
    chipDisplay = `${time[availableTimeIntervals[0]]}${
      timeIntervalAbbreviations[0]
    }`;
  }

  return chipDisplay;
}
