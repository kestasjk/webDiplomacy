import ConvoyGroupClass from "./ConvoyGroupClass";
import TerritoryClass from "./TerritoryClass";

import { UnitType } from "./enums";
import { IUnit } from "./Interfaces";

export default class UnitClass {
  id!: string;

  countryID!: string;

  convoyLink!: boolean;

  type!: keyof typeof UnitType;

  terrID!: string;

  Territory!: TerritoryClass;

  ConvoyGroup!: ConvoyGroupClass;

  constructor(data: IUnit) {
    Object.assign(this, { ...data, convoyLink: false });
  }

  setTerritory(territory: TerritoryClass) {
    this.Territory = territory;
  }

  setConvoyGroup(convoyGroup: ConvoyGroupClass) {
    this.ConvoyGroup = convoyGroup;
  }

  setConvoyLink() {
    this.convoyLink = true;
  }

  /**
   *
   * @param b border object
   * @returns {boolean}
   */
  canCrossBorder(b): boolean {
    if (
      (this.type === UnitType.Army && !b.a) ||
      (this.type === UnitType.Fleet && !b.f)
    ) {
      return false;
    }

    return true;
  }
}
