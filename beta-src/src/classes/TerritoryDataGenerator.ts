import { Territory } from "../interfaces";
import TerritoryEnum from "../enums/Territory";
import Territories from "../data/Territories";

export default class TerritoryDataGenerator implements Territory {
  public name: Territory["name"];

  public abbr: Territory["abbr"];

  public type: Territory["type"];

  constructor(terr: TerritoryEnum) {
    this.name = Territories[terr].name;
    this.abbr = Territories[terr].abbr;
    this.type = Territories[terr].type;
  }

  get territory(): Territory {
    return {
      name: this.name,
      abbr: this.abbr,
      type: this.type,
    };
  }
}
