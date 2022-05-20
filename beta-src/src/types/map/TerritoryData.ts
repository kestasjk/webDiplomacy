import TerritoryEnum from "../../enums/map/variants/classic/Territory";
import { TerritoryI } from "../../interfaces";

type TerritoryData = {
  [key in TerritoryEnum]: TerritoryI;
};

export default TerritoryData;
