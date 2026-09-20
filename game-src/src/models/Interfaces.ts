
export interface IBoard {
  context: IContext;
  territories: ITerritory[];
  terrStatus: IProvinceStatus[];
  units: IUnit[];
}

export interface ITerritory {
  coast: string;
  // This country ID does not appear to change over the course of the game.
  // It indicates the initial home ownership of a territory.
  countryID: string;
  coastParentID: string;
  id: string;
  name: string;
  supply: string;
  type: string;
  Borders: IBorder[];
  CoastalBorders: ICoastalBorder[];
}

export interface IBorder {
  id: string;
  a: boolean;
  f: boolean;
}

export interface ICoastalBorder {
  id: string;
  a: boolean;
  f: boolean;
}

export interface ICenter {
  countryID: number;
  terrID: number;
}

// What webdip api gives for LIVE units (in gameData response)
export interface IUnit {
  id: string;
  countryID: string;
  type: string;
  terrID: string;
}

// What webdip api gives for HISTORICAL units (in gameStatus response)
export interface IUnitHistorical {
  unitType: string;
  retreating: string;
  terrID: number;
  countryID: number;
}

export interface IProvinceStatus {
  id: string;
  // occupiedFromTerrID is used to mark where a unit came from when moving
  // in to occupy another, and is used to determine what the legal retreat
  // locations are for dislodged units. HOWEVER you cannot rely on this always
  // to be non-null. It is null in case of a dislodgement-by-convoy due to
  // needing to adjudicate certain convoy dislogement cornercases correctly.
  // So this can NOT be relied on as an indicator of when a unit occupies
  // another territory, it can ONLY be used for adjudicating retreat locations.
  occupiedFromTerrID: string | null;
  ownerCountryID: string | null;
  standoff: boolean;
  // The unit that is currently in this province. In case of dislodgment,
  // this is the dislodger, not the dislodged piece.
  unitID: string | null;
}

export interface IContext {
  countryID: string;
  gameID: number;
  memberID: number;
  maxOrderID: string;
  orderStatus: string;
  phase: string;
  tokenExpireTime: number;
  turn: number;
  userID: number;
  variantID: number;
}

export interface IConvoyGroup {
  armies: IUnit[];
  coasts: ITerritory[];
  fleets: IUnit[];
}

export interface IOrderData {
  convoyPath?: string[];
  error: string | null;
  fixed?: string[];
  fromTerrID: string | null;
  id: string;
  saved?: boolean;
  status: string;
  toTerrID: string | null;
  type: string | null;
  unitID: string;
  // Can be null on retreats or other moves where convoying doesn't make sense
  // Otherwise equal to "Yes" or "No".
  viaConvoy: string | null;
  countryID: number;
}

export interface IOrderDataHistorical {
  countryID: number;
  dislodged: string;
  fromTerrID: number;
  phase: string;
  success: string;
  terrID: number;
  toTerrID: number;
  turn: number;
  type: string;
  unitType: string;
  viaConvoy: string;
  // drawAsUnsaved is only present and set to true locally / internally when we 
  // want an order to be drawn in a way that indicates it is unsaved.
  drawAsUnsaved?: boolean; 
}

export interface IPhaseDataHistorical {
  centers: ICenter[];
  orders: IOrderDataHistorical[];
  phase: string;
  turn: number;
  units: IUnitHistorical[];
}