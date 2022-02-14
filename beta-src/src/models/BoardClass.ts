import ConvoyGroupClass from "./ConvoyGroupClass";
import { IUnit, ITerritory, IContext, ITerrStatus } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

export default class BoardClass {
  territories: TerritoryClass[] = [];

  units: UnitClass[] = [];

  convoyGroups: ConvoyGroupClass[] = [];

  constructor(
    territories: ITerritory[],
    units: IUnit[],
    terrStatus: ITerrStatus[],
    public context: IContext,
  ) {
    /**
     * to reduce time, any territories that is either parent or child are stored and proccessed later in line 61
     */
    const coastParents: TerritoryClass[] = [];
    const coastChildren: TerritoryClass[] = [];

    this.territories = territories.map((territory) => {
      const curTerrStatus = terrStatus.find((ts) => ts.id === territory.id);

      let curTerritory;

      if (curTerrStatus) {
        curTerritory = new TerritoryClass(territory, curTerrStatus);

        const curUnit = curTerrStatus.unitID
          ? new UnitClass(units[curTerrStatus.unitID])
          : null;

        if (curUnit) {
          curTerritory.setUnit(curUnit);

          curUnit.setTerritory(curTerritory);

          this.units.push(curUnit);
        }

        if (curTerritory.coast === "Parent") {
          coastParents.push(curTerritory);
        }

        if (curTerritory.coast === "Child") {
          coastChildren.push(curTerritory);
        }

        if (curTerritory.coastParentID === curTerritory.id) {
          curTerritory.coastParent = curTerritory;
        }
      }

      return curTerritory;
    });

    coastChildren.forEach((cc) => {
      const parent = coastParents.find((cp) => cp.id === cc.coastParentID);
      if (parent) {
        cc.setCoastParent(parent);
      }
    });

    /**
     * load and initialize convoyGroup.
     * convoyGroup has
     * fleets that are adjacent
     * coasts that are adjacent above fleets
     * armies that are placed in above coasts
     */
    if (context.phase === "Diplomacy") {
      this.units.forEach((u) => {
        if (u.type === "Fleet" && u.Territory.type === "Sea") {
          const newConvoyGroup = new ConvoyGroupClass(this);

          /**
           * first load fleets, and coasts with armies within them.
           */
          newConvoyGroup.loadFleet(u);
          newConvoyGroup.loadCoasts();
          this.convoyGroups.push(newConvoyGroup);
        }
      });

      this.convoyGroups.forEach((cg) => cg.linkGroups());
      this.convoyGroups.forEach((cg) => cg.linkGroups());
    }
  }

  /**
   * finds adjacent territories
   */
  getBorderTerritories(territory: TerritoryClass) {
    const borderIDs = territory.Borders.map((b) => b.id);

    return this.territories.filter((t) => borderIDs.includes(`${t.id}`));
  }

  /**
   * find units on border territories
   */
  getBorderUnits(territory: TerritoryClass) {
    const borderTerritories = this.getBorderTerritories(territory.coastParent);

    return borderTerritories.map((bt) => bt.Unit);
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

    const convoyableTerritories = Array.from(unit.ConvoyGroup.coasts).reduce(
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
        unit.ConvoyGroup.coasts.has(targetTerritory)
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
}
