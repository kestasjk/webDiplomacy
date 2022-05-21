import Territory from "../../enums/map/variants/classic/Territory";
import GameStateMap from "../../types/state/GameStateMap";

type TerritoryToID = { [key in Territory]?: string };
type IDToTerritory = { [key: string]: Territory };
export default interface GameStateMaps {
  territoryToUnit: TerritoryToID;
  unitToOrder: GameStateMap;
  unitToTerritory: IDToTerritory;
  territoryToTerrID: TerritoryToID;
  terrIDToTerritory: IDToTerritory;
}
