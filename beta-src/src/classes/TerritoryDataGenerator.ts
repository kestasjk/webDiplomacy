import { TerritoryI } from "../interfaces";
import TerritoryEnum from "../enums/map/variants/classic/Territory";
import Territories from "../data/Territories";

export default class TerritoryDataGenerator implements TerritoryI {
  public abbr: TerritoryI["abbr"];

  public name: TerritoryI["name"];

  public type: TerritoryI["type"];

  constructor(terr: TerritoryEnum) {
    this.abbr = Territories[terr].abbr;
    this.name = Territories[terr].name;
    this.type = Territories[terr].type;
  }

  get territory(): TerritoryI {
    return {
      abbr: this.abbr,
      name: this.name,
      type: this.type,
    };
  }
}
