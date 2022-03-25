import TerritoryEnum from "../../enums/Territory";
import { Territory } from "../../interfaces";

type TerritoryData = {
  [key in TerritoryEnum]: Territory;
};

export default TerritoryData;
