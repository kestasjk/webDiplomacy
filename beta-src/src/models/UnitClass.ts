import { IUnit } from "./Interfaces";

export default class UnitClass {
  id: number;

  terrID: number;

  countryID: number;

  type: string;

  constructor({ id, terrID, countryID, type }: IUnit) {
    this.id = id;
    this.terrID = terrID;
    this.countryID = countryID;
    this.type = type;
  }
}
