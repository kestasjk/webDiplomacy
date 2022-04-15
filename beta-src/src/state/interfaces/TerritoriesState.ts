import Territory from "../../enums/map/variants/classic/Territory";

export interface TerritoryMeta {
  coast: string;
  coastParentID: string;
  countryID: string | null;
  id: string;
  name: string;
  ownerCountryID: string | null;
  standoff: boolean;
  supply: boolean;
  territory: Territory;
  type: string;
  unitID: string | null;
}

type TerritoriesMeta = {
  [key in Territory]?: TerritoryMeta;
};

export default TerritoriesMeta;
