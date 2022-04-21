interface ContextVar {
  context: {
    countryID: string;
    gameID: number;
    maxOrderID: string;
    memberID: number;
    orderStatus: string;
    phase: string;
    tokenExpireTime: number | null;
    turn: number;
    userID: number;
    variantID: number;
  };
  contextKey: string;
}

export default ContextVar;
