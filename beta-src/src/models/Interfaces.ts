export interface IBoard {
  territories: ITerritory[];
  units: IUnit[];
  myUnits: IUnit[];
  terrStatus: ITerrStatus[];
  context: IContext;
}

export interface ITerritory {
  id: number;
  name: string;
  type: string;
  supply: string;
  countryID: number;
  coast: string;
  coastParentID: number;
  smallMapX: number;
  smallMapY: number;
  Borders: Array<IBorder>;
  CoastalBorders: Array<ICoastalBorder>;
  coastParent: ITerritory;
  Unit: IUnit;
  unitID: number;
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

export interface IUnit {
  id: number;
  terrID: number;
  countryID: number;
  type: string;
}

export interface ITerrStatus {
  id: number;
  standoff: boolean;
  occupiedFromTerrID: number;
  unitID: number;
  ownerCountryID: number;
}

export interface IContext {
  gameID: number;
  variantID: number;
  userID: number;
  memberID: number;
  turn: number;
  phase: string;
  countryID: number;
  tokenExpireTime: number;
  maxOrderID: number;
  orderStatus: string;
}
