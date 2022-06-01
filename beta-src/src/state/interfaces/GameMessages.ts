export interface GameMessage {
  fromCountryID: number;
  message: string;
  timeSent: number;
  toCountryID: number;
  turn: number;
  unread: boolean;
}

export interface GameMessages {
  messages: GameMessage[];
  newMessagesFrom: number[];
  time: number;
  countryIDSelected: number;
}

export default GameMessages;
