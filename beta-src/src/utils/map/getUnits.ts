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
  phase: GameOverviewResponse["phase"],
): Unit[] {
  const unitsToDraw: Unit[] = [];
  const { territories, territoryStatuses, units } = data;
  Object.values(units).forEach((unit) => {
    const territory = territories[unit.terrID];
    const territoryStatus = territoryStatuses.find((t) => unit.terrID === t.id);
    const territoryHasMultipleUnits = Object.values(units).filter(
      (u) => u.terrID === unit.terrID,
    );
    const occupiedTerritory = territoryStatus?.occupiedFromTerrID
      ? territoryStatuses.find(
          (t) => territoryStatus?.occupiedFromTerrID === t.id,
        )
      : undefined;

    if (territory) {
      const mappedTerritory = TerritoryMap[territory.name];
      if (mappedTerritory) {
        const memberCountry = members.find(
          (member) => member.countryID.toString() === unit.countryID,
        );
        if (memberCountry) {
          const { country } = memberCountry;
          if (
            (territoryStatus?.occupiedFromTerrID &&
              unit.id !== territoryStatus.unitID &&
              occupiedTerritory?.ownerCountryID !== unit.countryID &&
              territoryHasMultipleUnits.length > 1 &&
              phase === "Retreats") ||
            (phase === "Retreats" && territoryHasMultipleUnits.length === 1) ||
            phase !== "Retreats"
          ) {
            unitsToDraw.push({
              country: countryMap[country],
              mappedTerritory,
              unit,
            });
          }
        }
      }
    }
  });
  return unitsToDraw;
}
