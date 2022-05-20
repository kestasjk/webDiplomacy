import GameStateMap from "../../types/state/GameStateMap";

export default interface GameStateMaps {
  territoryToUnit: GameStateMap;
  unitToOrder: GameStateMap;
  unitToTerritory: GameStateMap;
  enumToTerritory: GameStateMap;
  territoryToEnum: GameStateMap;
  coastalTerritories: string[];
}
