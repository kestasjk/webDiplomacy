import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoryMap, {
  MTerritory,
} from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Country from "../../enums/Country";
import { IUnit } from "../../models/Interfaces";

interface Unit {
  country: Country;
  mappedTerritory: MTerritory;
  unit: IUnit;
}

export default function getUnits(
  data: GameDataResponse["data"],
  members: GameOverviewResponse["members"],
  overview: GameOverviewResponse,
): Unit[] {
  const unitsToDraw: Unit[] = [];
  const { territories, territoryStatuses, units } = data;
  const { phase } = overview;
  Object.values(units).forEach((unit) => {
    let territory = territories[unit.terrID];
    const territoryStatus = territoryStatuses.find((t) => unit.terrID === t.id);
    const territoryHasMultipleUnits = Object.values(units).filter(
      (u) => u.terrID === unit.terrID,
    );
    const occupiedTerritory = territoryStatus?.occupiedFromTerrID
      ? territoryStatuses.find(
          (t) => territoryStatus?.occupiedFromTerrID === t.id,
        )
      : undefined;

    if (
      territoryStatus?.occupiedFromTerrID &&
      unit.id === territoryStatus.unitID &&
      occupiedTerritory?.ownerCountryID === unit.countryID &&
      territoryHasMultipleUnits.length > 1 &&
      phase === "Retreats"
    ) {
      territory = territories[territoryStatus.occupiedFromTerrID];
    }

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
