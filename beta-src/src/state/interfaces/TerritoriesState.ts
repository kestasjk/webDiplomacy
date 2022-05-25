import Territory from "../../enums/map/variants/classic/Territory";

export interface TerritoryMeta {
  coast: string; // FIXME: this is incorrectly set to "no" for coasts!
  coastParentID: string;
  countryID: string | null;
  id: string;
  ownerCountryID: string | null;
  standoff: boolean;
  supply: boolean;
  territory: Territory;
  type: string;
  unitID: string | null;
  coastChildIDs: string[];
}

type TerritoriesMeta = {
  [key in Territory]?: TerritoryMeta;
};

export default TerritoriesMeta;
