import ContextVar from "../../interfaces/state/ContextVar";
import {
  ITerritory,
  ITerrStatus,
  IUnit,
  IOrderData,
} from "../../models/Interfaces";

export interface APITerritories {
  [key: string]: ITerritory;
}

interface GameData {
  contextVars?: ContextVar;
  currentOrders?: IOrderData[];
  territories: APITerritories;
  units: {
    // Key is the Unit ID.
    [key: string]: IUnit;
  };
  territoryStatuses: ITerrStatus[];
}

interface GameDataResponse {
  msg: string;
  referenceCode: string;
  success: boolean;
  data: GameData;
}

export default GameDataResponse;
