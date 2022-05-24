import BoardClass from "./BoardClass";
import UnitClass from "./UnitClass";

export interface IBoard {
  context: IContext;
  territories: ITerritory[];
  terrStatus: ITerrStatus[];
  units: IUnit[];
}

export interface ITerritory {
  coast: string;
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
  countryID: string;
  terrID: string;
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

export interface ITerrStatus {
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

export interface IOrder {
  board: BoardClass;
  orderData: IOrderData;
  unit: UnitClass;
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
  type: string;
  unitID: string;
  viaConvoy: string | null; // TODO when is this ever null???
}

export interface IOrderDataHistorical {
  countryID: string;
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
}

export interface IPhaseDataHistorical {
  centers: ICenter[];
  orders: IOrderDataHistorical[];
  phase: string;
  turn: number;
  units: IUnitHistorical[];
}