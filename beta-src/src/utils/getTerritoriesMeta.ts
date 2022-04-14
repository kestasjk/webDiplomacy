import TerritoryMap from "../data/map/variants/classic/TerritoryMap";
import { ITerrStatus } from "../models/Interfaces";
import { APITerritories } from "../state/interfaces/GameDataResponse";
import TerritoriesMeta from "../state/interfaces/TerritoriesState";

export default function getTerritoriesMeta(data): TerritoriesMeta {
  const {
    territories,
    territoryStatuses,
  }: { territories: APITerritories; territoryStatuses: ITerrStatus[] } = data;

  const territoriesMeta: TerritoriesMeta = {};

  Object.entries(territories).forEach(
    ([
      id,
      { coast, coastParentID, countryID: homeCountryID, name, supply, type },
    ]) => {
      const mappedTerritory = TerritoryMap[name];
      const territoryStatus = territoryStatuses.find(({ id: territoryID }) => {
        return id === territoryID;
      });

      const countryID: string | null =
        homeCountryID === "0" ? null : homeCountryID;
      let ownerCountryID: string | null = null;
      if (territoryStatus) {
        ownerCountryID =
          territoryStatus.ownerCountryID === "0"
            ? null
            : territoryStatus.ownerCountryID;
      }

      const { territory } = mappedTerritory;
      if (!territoriesMeta[territory]) {
        territoriesMeta[territory] = {
          coast,
          coastParentID,
          countryID,
          id,
          name,
          ownerCountryID,
          standoff: territoryStatus ? territoryStatus.standoff : false,
          supply: supply === "Yes",
          territory,
          type,
          unitID: territoryStatus ? territoryStatus.unitID : null,
        };
      }
    },
  );

  return territoriesMeta;
}
