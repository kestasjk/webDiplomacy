export interface GameMessage {
  fromCountryID: string;
  message: string;
  timeSent: string;
  toCountryID: string;
  turn: string;
}

export interface GameMessages {
  messages: GameMessage[];
}

export default GameMessages;
