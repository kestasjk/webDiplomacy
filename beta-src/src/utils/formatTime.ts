import { ParsedTime } from "../interfaces/ParsedTime";

export default function formatTime(time: ParsedTime): string {
  const availableTimeIntervals = Object.keys(time).filter((key) => time[key]);

  if (availableTimeIntervals.length > 1) {
    return `${time[availableTimeIntervals[0]]}${availableTimeIntervals[0]} ${
      time[availableTimeIntervals[1]]
    }${availableTimeIntervals[1]} remaining`;
  }
  if (availableTimeIntervals.length === 0) {
    return "Time Up";
  }

  return `${time[availableTimeIntervals[0]]}${
    availableTimeIntervals[0]
  } remaining`;
}
