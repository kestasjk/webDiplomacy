import { webdipNameToTerritory } from "../../data/map/variants/classic/TerritoryMap";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

export default function generateMaps(
  data: GameDataResponse["data"],
): GameStateMaps {
  const { currentOrders, territories, units } = data;
  const territoryToTerrID: GameStateMaps["territoryToTerrID"] = {};
  const terrIDToTerritory: GameStateMaps["terrIDToTerritory"] = {};
  const terrIDToRegionID: GameStateMaps["terrIDToProvinceID"] = {};
  const terrIDToUnit: GameStateMaps["terrIDToUnit"] = {};
  const regionIDToUnit: GameStateMaps["provinceIDToUnit"] = {};
  const unitToTerrID: GameStateMaps["unitToTerrID"] = {};
  const territoryToUnit: GameStateMaps["territoryToUnit"] = {};
  const unitToTerritory: GameStateMaps["unitToTerritory"] = {};
  const unitToOrder: GameStateMaps["unitToOrder"] = {};

  Object.values(territories).forEach(({ id, name, coastParentID }) => {
    const territory = webdipNameToTerritory[name];
    territoryToTerrID[territory] = id;
    terrIDToTerritory[id] = territory;
    terrIDToRegionID[id] = coastParentID || id;
  });

  Object.values(units).forEach(({ id, terrID }) => {
    terrIDToUnit[terrID] = id;
    regionIDToUnit[terrIDToRegionID[terrID]] = id;
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
    terrIDToProvinceID: terrIDToRegionID,
    terrIDToUnit,
    provinceIDToUnit: regionIDToUnit,
    unitToTerrID,
    territoryToUnit,
    unitToTerritory,
    unitToOrder,
  };
}
