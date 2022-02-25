import Country from "../enums/Country";
import IntegerRange from "../types/IntegerRange";
import Vote from "../types/Vote";

export interface CountryTableData {
  abbr: string;
  bet: IntegerRange<5, 96>;
  centerQty: number;
  color: string;
  delaysLeft: IntegerRange<0, 5>;
  power: Country;
  unitQty: number;
  votes?: Vote;
}
