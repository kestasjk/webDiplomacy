type Member = {
  country: string;
  countryID: number;
  id: number;
  online: boolean;
  userID: number;
};

export interface GameState {
  apiStatus: "idle" | "loading" | "succeeded" | "failed";
  error: string | null | undefined;
  overview: {
    anon: string;
    drawType: string;
    excusedMissedTurns: number;
    gameOver: string;
    members: Member[];
    minimumBet: number;
    name: string;
    pauseTimeRemaining: number | null | undefined;
    phase: string;
    phaseMinutes: number;
    playerTypes: string;
    pot: number;
    potType: string;
    processStatus: string;
    processTime: number | null | undefined;
    startTime: number;
    turn: number;
    variant: {
      id: number;
      mapID: number;
      name: string;
      fullName: string;
      description: string;
      author: string;
      countries: string[];
      variantClasses: {
        drawMap: string;
        adjudicatorPreGame: string;
      };
      codeVersion: number | null | undefined;
      cacheVersion: number | null | undefined;
      coastParentIDByChildID: {
        [key: string]: number;
      };
      coastChildIDsByParentID: {
        [key: string]: number[];
      };
      terrIDByName: string | null | undefined;
      supplyCenterCount: number;
      supplyCenterTarget: number;
    };
    variantID: number;
  };
  status: {
    gameID: number;
    countryID: number;
    variantID: number;
    potType: string;
    turn: number;
    phase: string;
    gameOver: string;
    pressType: string;
    phases: string[];
    standoffs: string[];
    occupiedFrom: string[];
    votes: string | null;
    orderStatus: string;
    status: string;
  };
}
