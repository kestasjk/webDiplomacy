export interface GameMessage {
  fromCountryID: number;
  message: string;
  timeSent: number;
  toCountryID: number;
  turn: number;
}

export interface GameMessages {
  messages: GameMessage[];
}

export function mergeMessageArrays(
  msgs1: GameMessage[],
  msgs2: GameMessage[],
): GameMessage[] {
  const map = new Map();
  msgs1.forEach((msg) => {
    map.set(msg.timeSent, msg);
  });
  msgs2.forEach((msg) => {
    map.set(msg.timeSent, msg);
  });
  return Array.from(map.values()).sort((m1, m2) => m1.timeSent - m2.timeSent);
}

export default GameMessages;
