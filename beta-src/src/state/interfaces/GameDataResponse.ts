import ContextVar from "../../interfaces/state/ContextVar";
import {
  ITerritory,
  ITerrStatus,
  IUnit,
  IOrderData,
} from "../../models/Interfaces";

interface CountryID {
  contextVars: ContextVar;
  currentOrders: IOrderData[];
}

interface InvalidCountryID {
  countryID: number;
}

interface GameData {
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
  data: CountryID | InvalidCountryID | GameData;
}

export default GameDataResponse;
