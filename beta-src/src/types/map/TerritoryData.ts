import TerritoryEnum from "../../enums/map/variants/classic/Territory";
import { Territory } from "../../interfaces";

type TerritoryData = {
  [key in TerritoryEnum]: Territory;
};

export default TerritoryData;
