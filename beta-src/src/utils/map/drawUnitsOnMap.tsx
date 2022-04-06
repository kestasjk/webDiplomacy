import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import addUnitToTerritory from "./addUnitToTerritory";
import countryMap from "../../data/map/variants/classic/CountryMap";

export default function drawUnitsOnMap(
  members: GameOverviewResponse["members"],
  data: GameDataResponse["data"],
): void {
  if (data && members && "units" in data && "territories" in data) {
    const { territories, units } = data;
    Object.values(units).forEach((unit) => {
      const territory = territories[unit.terrID];
      if (territory) {
        const mappedTerritory = TerritoryMap[territory.name];
        if (mappedTerritory) {
          const memberCountry = members.find((member) => {
            return member.countryID.toString() === unit.countryID;
          });
          if (memberCountry) {
            const { country } = memberCountry;
            addUnitToTerritory(mappedTerritory, unit, countryMap[country]);
          }
        }
      }
    });
  }
}
