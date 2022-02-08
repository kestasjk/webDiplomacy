import { ITerritory, IBorder, ICoastalBorder } from "./Interfaces";
import UnitClass from "./UnitClass";

export default class Territory {
  id: number;

  name: string;

  type: string;

  supply: boolean;

  countryID: number;

  coast: string;

  coastParentID: number;

  smallMapX: number;

  smallMapY: number;

  Borders: IBorder[];

  CoastalBorders: ICoastalBorder[];

  convoyLink: boolean;

  coastParent: Territory;

  Unit: UnitClass;

  unitID: number;

  constructor({
    id,
    name,
    type,
    supply,
    countryID,
    coast,
    coastParentID,
    smallMapX,
    smallMapY,
    Borders,
    CoastalBorders,
    coastParent,
    Unit,
    unitID,
  }: ITerritory) {
    this.id = id;
    this.name = name;
    this.type = type;
    this.countryID = countryID;
    this.coast = coast;
    this.coastParentID = coastParentID;
    this.smallMapX = smallMapX;
    this.smallMapY = smallMapY;
    this.Borders = Borders;
    this.CoastalBorders = CoastalBorders;
    this.coastParent = new Territory(coastParent);
    this.Unit = Unit;
    this.unitID = unitID;

    this.supply = supply === "Yes";

    this.convoyLink = false;
  }
}
