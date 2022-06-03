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
// FIXME maybe we can make this whole thing more typesafe if instead of direct-accessing
// the fields, we instead use some accessor methods, which can enforce that you pass in
// the appropriate type as a key, instead of just "string".
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
