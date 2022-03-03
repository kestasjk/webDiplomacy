import { ParsedTime } from "../interfaces/ParsedTime";

export default function calculateAndSortRemainingTime(
  endTime: number,
): ParsedTime {
  const difference = endTime - +new Date() / 1000;

  let timeLeft = {
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
  };

  if (difference > 0) {
    timeLeft = {
      days: Math.floor(difference / (60 * 60 * 24)),
      hours: Math.floor((difference / (60 * 60)) % 24),
      minutes: Math.floor((difference / 60) % 60),
      seconds: Math.floor(difference % 60),
    };
  }

  return timeLeft;
}
