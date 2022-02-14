import ConvoyGroupClass from "./ConvoyGroupClass";
import { ITerritory, IBorder, ICoastalBorder, ITerrStatus } from "./Interfaces";
import UnitClass from "./UnitClass";

export default class TerritoryClass {
  id!: string;

  countryID!: string;

  coast!: string;

  coastParent!: TerritoryClass;

  coastParentID!: string;

  convoyLink!: boolean;

  name!: string;

  occupiedFromTerrID!: string;

  ownerCountryID!: string;

  smallMapX!: number;

  smallMapY!: number;

  supply!: boolean;

  standoff!: boolean;

  type!: string;

  unitID?: string;

  Borders!: IBorder[];

  CoastalBorders!: ICoastalBorder[];

  ConvoyGroup!: ConvoyGroupClass;

  ConvoyGroups!: ConvoyGroupClass[];

  Unit!: UnitClass;

  constructor(terrData: ITerritory, terrStatusData?: ITerrStatus) {
    Object.assign(this, {
      ...terrData,
      supply: terrData.supply === "Yes",
      ConvoyGroups: [],
      convoyLink: false,
    });

    if (terrStatusData) {
      Object.assign(this, terrStatusData);
    }
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
