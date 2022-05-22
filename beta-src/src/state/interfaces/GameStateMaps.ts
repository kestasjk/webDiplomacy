import Territory from "../../enums/map/variants/classic/Territory";
import GameStateMap from "../../types/state/GameStateMap";

type IDToTerritory = { [key: string]: Territory };
export default interface GameStateMaps {
  terrIDToUnit: GameStateMap;
  unitToOrder: GameStateMap;
  unitToTerrID: GameStateMap;
  territoryToTerrID: GameStateMap;
  terrIDToTerritory: IDToTerritory;
}
