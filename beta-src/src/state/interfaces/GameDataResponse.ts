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
  territoryStatuses: IProvinceStatus[];
}

interface GameDataResponse {
  msg: string;
  referenceCode: string;
  success: boolean;
  data: GameData;
}

export default GameDataResponse;
