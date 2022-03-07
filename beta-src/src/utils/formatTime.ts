import { ParsedTime } from "../interfaces/ParsedTime";

export default function formatTime(time: ParsedTime) {
  const availableTimeIntervals = Object.keys(time).filter((key) => time[key]);
  const timeIntervalAbbreviations = availableTimeIntervals.map(
    (interval: string) => {
      return interval.charAt(0);
    },
  );

  if (availableTimeIntervals.length > 1) {
    return `${time[availableTimeIntervals[0]]}${timeIntervalAbbreviations[0]} ${
      time[availableTimeIntervals[1]]
    }${timeIntervalAbbreviations[1]}`;
  }
  if (availableTimeIntervals.length === 0) {
    return "Time Up";
  }

  return `${time[availableTimeIntervals[0]]}${timeIntervalAbbreviations[0]}`;
}
