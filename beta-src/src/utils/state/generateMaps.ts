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
  const unitToTerrID: GameStateMaps["unitToTerrID"] = {};
  const territoryToUnit: GameStateMaps["territoryToUnit"] = {};
  const unitToTerritory: GameStateMaps["unitToTerritory"] = {};
  const unitToOrder: GameStateMaps["unitToOrder"] = {};

  Object.values(territories).forEach(({ id, name }) => {
    const territory = webdipNameToTerritory[name];
    territoryToTerrID[territory] = id;
    terrIDToTerritory[id] = territory;
  });

  Object.values(units).forEach(({ id, terrID }) => {
    terrIDToUnit[terrID] = id;
    unitToTerrID[id] = terrID;
    const territory = terrIDToTerritory[terrID];
    territoryToUnit[territory] = id;
    unitToTerritory[id] = territory;
  });

  currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    territoryToTerrID,
    terrIDToTerritory,
    terrIDToUnit,
    unitToTerrID,
    territoryToUnit,
    unitToTerritory,
    unitToOrder,
  };
}
