import { webdipNameToTerritory } from "../../data/map/variants/classic/TerritoryMap";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

export default function generateMaps(
  data: GameDataResponse["data"],
): GameStateMaps {
  const { currentOrders, territories, units } = data;
  const territoryToTerrID: GameStateMaps["territoryToTerrID"] = {};
  const terrIDToTerritory: GameStateMaps["terrIDToTerritory"] = {};
  const territoryToUnit: GameStateMaps["territoryToUnit"] = {};
  const unitToOrder: GameStateMaps["unitToOrder"] = {};
  const unitToTerritory: GameStateMaps["unitToTerritory"] = {};

  Object.values(territories).forEach(({ id, name }) => {
    const territory = webdipNameToTerritory[name];
    territoryToTerrID[territory] = id;
    terrIDToTerritory[id] = territory;
  });

  Object.values(units).forEach(({ id, terrID }) => {
    const territory = terrIDToTerritory[terrID];
    territoryToUnit[territory] = id;
    unitToTerritory[id] = territory;
  });

  currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    territoryToUnit,
    unitToOrder,
    unitToTerritory,
    territoryToTerrID,
    terrIDToTerritory,
  };
}
