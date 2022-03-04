import { ParsedTime } from "../interfaces/ParsedTime";

export default function parseSeconds(seconds: number): ParsedTime {
  let timeLeft: ParsedTime = {
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
  };

  if (seconds > 0) {
    timeLeft = {
      days: Math.floor(seconds / (60 * 60 * 24)),
      hours: Math.floor((seconds / (60 * 60)) % 24),
      minutes: Math.floor((seconds / 60) % 60),
      seconds: Math.floor(seconds % 60),
    };
  }

  return timeLeft;
}
