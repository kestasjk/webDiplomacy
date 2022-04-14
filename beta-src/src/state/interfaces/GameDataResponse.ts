import ContextVar from "../../interfaces/state/ContextVar";
import {
  ITerritory,
  ITerrStatus,
  IUnit,
  IOrderData,
} from "../../models/Interfaces";

interface InvalidCountryID {
  countryID: number;
}

export interface APITerritories {
  [key: string]: ITerritory;
}

interface GameData {
  contextVars?: ContextVar;
  currentOrders?: IOrderData[];
  territories: APITerritories;
  units: {
    [key: string]: IUnit;
  };
  territoryStatuses: ITerrStatus[];
}

interface GameDataResponse {
  msg: string;
  referenceCode: string;
  success: boolean;
  data: GameData | InvalidCountryID;
}

export default GameDataResponse;
