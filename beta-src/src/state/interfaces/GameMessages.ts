export interface GameMessage {
  fromCountryID: number;
  message: string;
  timeSent: number;
  toCountryID: number;
  turn: number;
}

export interface GameMessages {
  messages: GameMessage[];
  newMessagesFrom: number[];
  time: number;
  outstandingRequests: number;
}

export default GameMessages;
