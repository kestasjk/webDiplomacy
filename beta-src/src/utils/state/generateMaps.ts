import { webdipNameToTerritory } from "../../data/map/variants/classic/TerritoryMap";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

export default function generateMaps(
  data: GameDataResponse["data"],
): GameStateMaps {
  const { currentOrders, territories, units } = data;
  const territoryToTerrID: GameStateMaps["territoryToTerrID"] = {};
  const terrIDToTerritory: GameStateMaps["terrIDToTerritory"] = {};
  const terrIDToUnit: GameStateMaps["terrIDToUnit"] = {};
  const unitToOrder: GameStateMaps["unitToOrder"] = {};
  const unitToTerrID: GameStateMaps["unitToTerrID"] = {};

  Object.values(territories).forEach(({ id, name }) => {
    const territory = webdipNameToTerritory[name];
    territoryToTerrID[territory] = id;
    terrIDToTerritory[id] = territory;
  });

  Object.values(units).forEach(({ id, terrID }) => {
    terrIDToUnit[terrID] = id;
    unitToTerrID[id] = terrID;
  });

  currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    terrIDToUnit,
    unitToOrder,
    unitToTerrID,
    territoryToTerrID,
    terrIDToTerritory,
  };
}
