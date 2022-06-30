import TerritoryMap, {
  webdipNameToTerritory,
} from "../../data/map/variants/classic/TerritoryMap";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

export default function generateMaps(
  data: GameDataResponse["data"],
): GameStateMaps {
  console.log({ data });
  const { territories, units } = data;
  const territoryToTerrID: GameStateMaps["territoryToTerrID"] = {};
  const terrIDToTerritory: GameStateMaps["terrIDToTerritory"] = {};
  const terrIDToProvinceID: GameStateMaps["terrIDToProvinceID"] = {};
  const terrIDToProvince: GameStateMaps["terrIDToProvince"] = {};
  const provinceIDToUnits: GameStateMaps["provinceIDToUnits"] = {};
  const unitToTerrID: GameStateMaps["unitToTerrID"] = {};
  const provinceToUnits: GameStateMaps["provinceToUnits"] = {};
  const unitToTerritory: GameStateMaps["unitToTerritory"] = {};
  const unitToOrder: GameStateMaps["unitToOrder"] = {};

  Object.values(territories).forEach(({ id, name, coastParentID }) => {
    const territory = webdipNameToTerritory[name];
    territoryToTerrID[territory] = id;
    terrIDToTerritory[id] = territory;
    terrIDToProvinceID[id] = coastParentID || id;
    terrIDToProvince[id] = TerritoryMap[territory].province;
  });

  Object.values(units).forEach(({ id, terrID }) => {
    const provinceID = terrIDToProvinceID[terrID];
    if (!provinceIDToUnits[provinceID]) provinceIDToUnits[provinceID] = [];
    provinceIDToUnits[terrIDToProvinceID[terrID]].push(id);
    unitToTerrID[id] = terrID;
    const territory = terrIDToTerritory[terrID];
    const province = terrIDToProvince[terrID];
    if (!provinceToUnits[province]) provinceToUnits[province] = [];
    provinceToUnits[province].push(id);
    unitToTerritory[id] = territory;
  });

  data.currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    territoryToTerrID,
    terrIDToTerritory,
    terrIDToProvinceID,
    terrIDToProvince,
    provinceIDToUnits,
    unitToTerrID,
    provinceToUnits,
    unitToTerritory,
    unitToOrder,
  };
}
