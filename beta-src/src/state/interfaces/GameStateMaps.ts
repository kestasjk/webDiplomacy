import GameStateMap from "../../types/state/GameStateMap";

export default interface GameStateMaps {
  territoryToUnit: GameStateMap;
  unitToOrder: GameStateMap;
  unitToTerritory: GameStateMap;
}
