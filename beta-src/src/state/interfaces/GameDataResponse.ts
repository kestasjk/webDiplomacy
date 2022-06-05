import ContextVar from "../../interfaces/state/ContextVar";
import {
  ITerritory,
  IProvinceStatus,
  IUnit,
  IOrderData,
} from "../../models/Interfaces";

export interface APITerritories {
  [key: string]: ITerritory;
}

export interface GameData {
  contextVars?: ContextVar;
  currentOrders?: IOrderData[];
  territories: APITerritories;
  units: {
    // Key is the Unit ID.
    [key: string]: IUnit;
  };
  // This array is very quirky, beware.
  // Its entries correspond to provinces rather than territories
  // and it may NOT be complete - some provinces that "don't have much going on"
  // may be omitted entirely from this array, and one is supposed to assume that
  // all the fields of such a missing province are the "default" values.
  territoryStatuses: IProvinceStatus[];
}

interface GameDataResponse {
  msg: string;
  referenceCode: string;
  success: boolean;
  data: GameData;
}

export default GameDataResponse;
