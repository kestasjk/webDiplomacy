export enum MessageStatus {
  READ,
  UNREAD,
  UNKNOWN,
}

export interface GameMessage {
  fromCountryID: number;
  message: string;
  timeSent: number;
  toCountryID: number;
  turn: number;
  status: MessageStatus;
  phaseMarker: string;
}

export interface GameMessages {
  messages: GameMessage[];
  newMessagesFrom: number[];
  time: number;
  countryIDSelected: number;
}

export default GameMessages;
