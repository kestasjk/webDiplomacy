import ContextVar from "../../interfaces/ContextVar";
import Order from "../../interfaces/Order";
import { ITerritory, ITerrStatus, IUnit } from "../../models/Interfaces";

interface CountryID {
  contextVars: ContextVar;
  currentOrders: Order[];
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
