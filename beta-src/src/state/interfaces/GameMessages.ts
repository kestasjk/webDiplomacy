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

export default GameMessages;
