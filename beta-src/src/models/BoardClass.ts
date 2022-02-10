import { IBoard, IContext, ITerrStatus } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

export default class BoardClass implements IBoard {
  myUnits: UnitClass[];

  constructor(
    public territories: TerritoryClass[],
    public units: UnitClass[],
    public terrStatus: ITerrStatus[],
    public context: IContext,
  ) {
    this.units = [];
    this.myUnits = [];

    this.territories = territories.map((territory) => {
      const curTerrStatus = this.terrStatus.find(
        (ts) => ts.id === territory.id,
      );

      const territoryObj = {
        ...territory,

        coastParent: territory,
        ...curTerrStatus,
      };

      const curUnit = curTerrStatus?.unitID
        ? new UnitClass(units[curTerrStatus.unitID])
        : null;

      if (curUnit) {
        this.units.push(curUnit);

        territoryObj.Unit = curUnit;
        territoryObj.unitID = curUnit.id;

        if (curUnit.countryID === territory.countryID) {
          this.myUnits.push(curUnit);
        }
      }

      if (territory.coastParentID !== territory.id) {
        const newCoastParent =
          territories.find((t) => t.id === territory.coastParentID) ||
          territory;

        territoryObj.coastParent = newCoastParent;
        territoryObj.supply = newCoastParent?.supply;
        territoryObj.Borders = newCoastParent?.Borders;
      }

      return new TerritoryClass(territoryObj);
    });
  }

  getBorderTerritories(territory: TerritoryClass) {
    const borderIDs = territory.Borders.map((b) => b.id);

    return this.territories.filter((t) => borderIDs.includes(`${t.id}`));
  }

  getBorderUnits(territory: TerritoryClass) {
    const borderTerritories = this.getBorderTerritories(territory.coastParent);

    return borderTerritories.map((bt) => bt.Unit);
  }

  canMoveInto(unit: UnitClass, targetTerritory: TerritoryClass) {
    if (
      this.getMovableTerritories(unit)
        .map((movableTerritories) => {
          return movableTerritories.coastParent.id;
        })
        .includes(targetTerritory.coastParent.id)
    ) {
      return true;
    }
    return false;
  }

  // Can I move to a given Territory via convoy (must be an army on a coast)
  static canConvoyTo(targetTerritory: TerritoryClass, unit: UnitClass) {
    if (unit.type === "Army") {
      // Can't get convoyed to our own territory
      if (targetTerritory.id === unit.Territory.id) return false;

      // We're in a convoy group, moving into a convoygroup territory which is in our convoygroup.
      if (
        unit.convoyLink &&
        targetTerritory.convoyLink &&
        unit.ConvoyGroup.coasts.includes(targetTerritory)
      )
        return true;
    }

    return false;
  }

  static canCrossBorder(unit: UnitClass, b) {
    if (unit.type === "Army" && !b.a) return false;
    if (unit.type === "Fleet" && !b.f) return false;
    return true;
  }

  getMovableTerritories(unit: UnitClass) {
    return unit.Territory.CoastalBorders.reduce(
      (acc: TerritoryClass[], cur) => {
        if (BoardClass.canCrossBorder(unit, cur)) {
          const borderTerritory = this.territories.find(
            (territory) => territory.id === cur.id,
          );

          if (borderTerritory) acc.push(borderTerritory);
        }

        return acc;
      },
      [],
    );
  }

  // Territories I can move to, including convoyable locations for an army
  getReachableTerritories(unit: UnitClass) {
    if (!unit.convoyLink && unit.type !== "Army") {
      return [];
    }

    const movableTerritories = this.getMovableTerritories(unit);
    const convoyableTerritories = unit.ConvoyGroup.coasts.reduce(
      (acc: TerritoryClass[], cur) => {
        if (BoardClass.canConvoyTo(cur, unit)) {
          acc.push(cur);
        }
        return acc;
      },
      [],
    );
    return Array.from(
      new Set(movableTerritories.concat(convoyableTerritories)),
    );
  }

  getMovableUnits(unit: UnitClass) {
    return this.getMovableTerritories(unit).map((movableTerritory) => {
      return movableTerritory.coastParent.Unit;
    });
  }
}
