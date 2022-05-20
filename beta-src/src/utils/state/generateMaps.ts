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
  const territoryToEnum: GameStateMaps["territoryToEnum"] = {};
  const coastalTerritories: GameStateMaps["coastalTerritories"] = [];

  Object.values(units).forEach(({ id, terrID }) => {
    territoryToUnit[terrID] = id;
    unitToTerritory[id] = terrID;
  });

  Object.values(territories).forEach(({ id, name }) => {
    enumToTerritory[TerritoryMap[name].territory] = id;
    territoryToEnum[id] = TerritoryMap[name].territory.toString();
  });

  Object.entries(TerritoryMap)
    .filter(([, territory]) => territory.parent !== undefined)
    .forEach(([, { territory }]) => {
      coastalTerritories.push(territory);
    });

  currentOrders?.forEach(({ id, unitID }) => {
    unitToOrder[unitID] = id;
  });

  return {
    territoryToUnit,
    unitToOrder,
    unitToTerritory,
    enumToTerritory,
    territoryToEnum,
    coastalTerritories,
  };
}
