import Country from "../enums/Country";
import IntegerRange from "../types/IntegerRange";
import { MemberData } from "./MemberData";

export interface CountryTableData extends MemberData {
  abbr: string;
  centerQty: number;
  color: string;
  delaysLeft: IntegerRange<0, 5>;
  power: Country;
  unitQty: number;
}
