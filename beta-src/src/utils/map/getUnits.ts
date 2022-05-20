import GameDataResponse, {
  APITerritories,
} from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoryMap, {
  MTerritory,
} from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Country from "../../enums/Country";
import { IUnit } from "../../models/Interfaces";
import UIState from "../../enums/UIState";

export interface Unit {
  country: Country;
  mappedTerritory: MTerritory;
  unit: IUnit;
}

export default function getUnits(
  territories: APITerritories,
  units: { [key: string]: IUnit },
  members: GameOverviewResponse["members"],
): Unit[] {
  const unitsToDraw: Unit[] = [];
  Object.values(units).forEach((unit) => {
    const territory = territories[unit.terrID];
    /* TODO break this for now 
    const territoryStatus = territoryStatuses.find((t) => unit.terrID === t.id);
    const territoryHasMultipleUnits = Object.values(units).filter(
      (u) => u.terrID === unit.terrID,
    );

    if (
      territoryStatus?.occupiedFromTerrID &&
      unit.id === territoryStatus.unitID &&
      territoryStatus?.ownerCountryID === unit.countryID &&
      territoryHasMultipleUnits.length > 1 &&
      contextVars?.context.phase === "Retreats"
    ) {
      territory = territories[territoryStatus.occupiedFromTerrID];
    }
    */

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
