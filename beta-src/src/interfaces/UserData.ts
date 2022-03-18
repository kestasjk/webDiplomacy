import Vote from "../types/Vote";
import { CountryTableData } from "./CountryTableData";

export interface UserData {
  /**
   * TODO
   * May need additional data
   */
  id: string;
  countryTableData: CountryTableData;
  votes: Vote;
}
