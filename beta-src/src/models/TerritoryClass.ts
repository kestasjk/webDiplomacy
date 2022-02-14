import ConvoyGroupClass from "./ConvoyGroupClass";
import { ITerritory, IBorder, ICoastalBorder, ITerrStatus } from "./Interfaces";
import UnitClass from "./UnitClass";

export default class TerritoryClass {
  id!: string;

  name!: string;

  type!: string;

  supply!: boolean;

  countryID!: string;

  coast!: string;

  coastParentID!: string;

  smallMapX!: number;

  smallMapY!: number;

  Borders!: IBorder[];

  CoastalBorders!: ICoastalBorder[];

  ConvoyGroup!: ConvoyGroupClass;

  ConvoyGroups!: ConvoyGroupClass[];

  convoyLink!: boolean;

  Unit!: UnitClass;

  standoff!: boolean;

  occupiedFromTerrID!: string;

  unitID?: string;

  ownerCountryID!: string;

  coastParent!: TerritoryClass;

  constructor(terrData: ITerritory, terrStatusData: ITerrStatus) {
    Object.assign(this, {
      ...terrData,
      ...terrStatusData,
      supply: terrData.supply === "Yes",
      convoyLink: false,
    });
  }

  setUnit(unit: UnitClass) {
    this.Unit = unit;
  }

  setCoastParent(coastParent: TerritoryClass) {
    const { Borders, supply } = coastParent;

    this.coastParent = coastParent;
    this.Borders = Borders;
    this.supply = supply;
  }

  setConvoyLink() {
    this.convoyLink = true;
  }

  setConvoyGroups(convoyGroup: ConvoyGroupClass) {
    this.ConvoyGroups.push(convoyGroup);
  }

  setConvoyGroup(convoyGroup: ConvoyGroupClass) {
    this.ConvoyGroup = convoyGroup;
  }
}
