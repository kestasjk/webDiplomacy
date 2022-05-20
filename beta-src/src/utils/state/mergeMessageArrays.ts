import { GameMessage } from "../../state/interfaces/GameMessages";

export default function mergeMessageArrays(
  msgs1: GameMessage[],
  msgs2: GameMessage[],
): GameMessage[] {
  const map = new Map();
  const msgs = msgs1.concat(msgs2);
  msgs.forEach((msg) => {
    // can't use array as key, so just mush it all into a string
    const key = `${msg.timeSent}:${msg.fromCountryID}:${msg.toCountryID}:${msg.message}`;
    map.set(key, msg);
  });
  console.log(`Merging ${msgs1.length} and ${msgs2.length} => ${map.size}`);

  return Array.from(map.values()).sort((m1, m2) => m1.timeSent - m2.timeSent);
}
