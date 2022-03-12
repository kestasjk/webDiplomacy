import { ParsedTime } from "../interfaces/ParsedTime";

const secondsInMinute = 60;
const minutesInHour = 60;
const hoursInDay = 24;

export default function parseSeconds(seconds: number): ParsedTime {
  let timeLeft: ParsedTime = {
    d: 0,
    h: 0,
    m: 0,
    s: 0,
  };

  if (seconds > 0) {
    timeLeft = {
      d: Math.floor(seconds / (secondsInMinute * minutesInHour * hoursInDay)),
      h: Math.floor((seconds / (secondsInMinute * minutesInHour)) % hoursInDay),
      m: Math.floor((seconds / secondsInMinute) % minutesInHour),
      s: Math.floor(seconds % secondsInMinute),
    };
  }

  return timeLeft;
}
