import ConvoyGroupClass from "./ConvoyGroupClass";
import { IUnit } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";

export default class UnitClass {
  id: string;

  terrID: string;

  countryID: string;

  type: string;

  convoyLink: boolean;

  Territory: TerritoryClass;

  ConvoyGroup: ConvoyGroupClass;

  constructor({
    id,
    terrID,
    countryID,
    type,
    Territory,
    ConvoyGroup,
    convoyLink = false,
  }: IUnit) {
    this.id = id;
    this.terrID = terrID;
    this.countryID = countryID;
    this.type = type;
    this.Territory = Territory;
    this.ConvoyGroup = ConvoyGroup;

    this.convoyLink = convoyLink;
  }
}
