export interface IBoard {
  territories: ITerritory[];
  units: IUnit[];
  myUnits: IUnit[];
  terrStatus: ITerrStatus[];
  context: IContext;
}

export interface ITerritory {
  id: string;
  name: string;
  type: string;
  supply: boolean;
  countryID: string;
  coast: string;
  coastParentID: string;
  smallMapX: number;
  smallMapY: number;
  Borders: Array<IBorder>;
  CoastalBorders: Array<ICoastalBorder>;
  coastParent: ITerritory;
  Unit: IUnit;
  unitID: string;
  convoyLink: boolean;
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
  id: string;
  terrID: string;
  countryID: string;
  type: string;
  Territory: ITerritory;
  ConvoyGroup: IConvoyGroup;
  convoyLink: boolean;
}

export interface ITerrStatus {
  id: string;
  standoff: boolean;
  occupiedFromTerrID: string;
  unitID: string;
  ownerCountryID: string;
}

export interface IContext {
  gameID: string;
  variantID: string;
  userID: string;
  memberID: string;
  turn: number;
  phase: string;
  countryID: string;
  tokenExpireTime: number;
  maxOrderID: string;
  orderStatus: string;
}

export interface IConvoyGroup {
  coasts: ITerritory[];
  armies: IUnit[];
  fleets: IUnit[];
}

export interface IOrder {
  board: IBoard;
  unit: IUnit;
  orderData: IOrderData;
}

export interface IOrderData {
  error: string;
  status: string;
  id: string;
  type: string;
  unitId: string;
  toTerrID: string;
  fromTerrID: string;
  viaConvoy: string;
}
