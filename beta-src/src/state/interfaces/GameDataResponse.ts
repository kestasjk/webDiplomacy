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

interface GameData {
  contextVars?: ContextVar;
  currentOrders?: IOrderData[];
  territories: {
    [key: string]: ITerritory;
  };
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
