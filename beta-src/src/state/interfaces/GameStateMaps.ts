import Territory from "../../enums/map/variants/classic/Territory";
import GameStateMap from "../../types/state/GameStateMap";

type IDToTerritory = { [key: string]: Territory };
export default interface GameStateMaps {
  territoryToTerrID: GameStateMap;
  terrIDToTerritory: IDToTerritory;
  terrIDToUnit: GameStateMap;
  unitToTerrID: GameStateMap;
  territoryToUnit: GameStateMap;
  unitToTerritory: IDToTerritory;
  unitToOrder: GameStateMap;
}
