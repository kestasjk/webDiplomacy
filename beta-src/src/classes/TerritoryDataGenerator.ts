import { Territory } from "../interfaces";
import TerritoryEnum from "../enums/Territory";
import Territories from "../data/Territories";

export default class TerritoryDataGenerator implements Territory {
  public abbr: Territory["abbr"];

  public name: Territory["name"];

  public type: Territory["type"];

  constructor(terr: TerritoryEnum) {
    this.abbr = Territories[terr].abbr;
    this.name = Territories[terr].name;
    this.type = Territories[terr].type;
  }

  get territory(): Territory {
    return {
      abbr: this.abbr,
      name: this.name,
      type: this.type,
    };
  }
}
