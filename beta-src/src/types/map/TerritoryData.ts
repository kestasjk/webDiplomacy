import Territory from "../../enums/map/variants/classic/Territory";
import { TerritoryI } from "../../interfaces";

type TerritoryData = {
  [key in Territory]: TerritoryI;
};

export default TerritoryData;
