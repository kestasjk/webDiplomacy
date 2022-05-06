import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

export default function generateMaps(
  data: GameDataResponse["data"],
): GameStateMaps {
  const { currentOrders, territories, units } = data;
  const territoryToUnit: GameStateMaps["territoryToUnit"] = {};
  const unitToOrder: GameStateMaps["unitToOrder"] = {};
  const unitToTerritory: GameStateMaps["unitToTerritory"] = {};
  const enumToTerritory: GameStateMaps["enumToTerritory"] = {};

  Object.values(units).forEach(({ id, terrID }) => {
    territoryToUnit[terrID] = id;
    unitToTerritory[id] = terrID;
  });

  Object.values(territories).forEach(({ id, name }) => {
    enumToTerritory[TerritoryMap[name].territory] = id;
  });

  currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    territoryToUnit,
    unitToOrder,
    unitToTerritory,
    enumToTerritory,
  };
}
