import { ParsedTime } from "../interfaces/ParsedTime";

const secondsInMinute = 60;
const minutesInHour = 60;
const hoursInDay = 24;
export default function parseSeconds(seconds: number): ParsedTime {
  let timeLeft: ParsedTime = {
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
  };

  if (seconds > 0) {
    timeLeft = {
      days: Math.floor(
        seconds / (secondsInMinute * minutesInHour * hoursInDay),
      ),
      hours: Math.floor(
        (seconds / (secondsInMinute * minutesInHour)) % hoursInDay,
      ),
      minutes: Math.floor((seconds / secondsInMinute) % minutesInHour),
      seconds: Math.floor(seconds % secondsInMinute),
    };
  }

  return timeLeft;
}
