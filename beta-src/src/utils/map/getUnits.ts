import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoryMap, {
  ITerritory,
} from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Country from "../../enums/Country";
import { IUnit } from "../../models/Interfaces";

interface Unit {
  country: Country;
  mappedTerritory: ITerritory;
  unit: IUnit;
}

export default function getUnits(
  data: GameDataResponse["data"],
  members: GameOverviewResponse["members"],
): Unit[] {
  const unitsToDraw: Unit[] = [];
  const { territories, units } = data;
  Object.values(units).forEach((unit) => {
    const territory = territories[unit.terrID];
    if (territory) {
      const mappedTerritory = TerritoryMap[territory.name];
      if (mappedTerritory) {
        const memberCountry = members.find(
          (member) => member.countryID.toString() === unit.countryID,
        );
        if (memberCountry) {
          const { country } = memberCountry;
          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit,
          });
        }
      }
    }
  });
  return unitsToDraw;
}
