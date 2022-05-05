interface GameMessagesArray {
  fromCountryID: string;
  message: string;
  timeSent: string;
  toCountryID: string;
  turn: string;
}

export interface GameMessages {
  messages: GameMessagesArray[];
  pressType: string;
  phase: string;
}

export default GameMessages;
