import Territory from "../../enums/map/variants/classic/Territory";
import Province from "../../enums/map/variants/classic/Province";

type GameStateMap = {
  [key: string]: string;
};
type GameStateMapMulti = {
  [key: string]: string[];
};

type IDToTerritory = { [key: string]: Territory };
type IDToProvince = { [key: string]: Province };
export default interface GameStateMaps {
  territoryToTerrID: GameStateMap;
  terrIDToTerritory: IDToTerritory;
  terrIDToProvinceID: GameStateMap;
  terrIDToProvince: IDToProvince;
  provinceIDToUnits: GameStateMapMulti; 
  unitToTerrID: GameStateMap;
  provinceToUnits: GameStateMapMulti;
  unitToTerritory: IDToTerritory;
  unitToOrder: GameStateMap;
}
