import Territory from "../../enums/map/variants/classic/Territory";

type GameStateMap = {
  [key: string]: string;
};

type IDToTerritory = { [key: string]: Territory };
export default interface GameStateMaps {
  territoryToTerrID: GameStateMap;
  terrIDToTerritory: IDToTerritory;
  terrIDToProvinceID: GameStateMap;
  terrIDToUnit: GameStateMap;
  provinceIDToUnit: GameStateMap; 
  unitToTerrID: GameStateMap;
  territoryToUnit: GameStateMap;
  unitToTerritory: IDToTerritory;
  unitToOrder: GameStateMap;
}
