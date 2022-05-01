import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMap";
import GameStateMap from "../../types/state/GameStateMap";

export default function generateMaps(
  data: GameDataResponse["data"],
): GameStateMaps {
  const { units, currentOrders } = data;
  const territoryToUnit: GameStateMap = {};
  const unitToOrder: GameStateMap = {};
  const unitToTerritory: GameStateMap = {};

  Object.values(units).forEach(({ id, terrID }) => {
    territoryToUnit[terrID] = id;
    unitToTerritory[id] = terrID;
  });

  currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    territoryToUnit,
    unitToOrder,
    unitToTerritory,
  };
}
