import ConvoyGroupClass from "./ConvoyGroupClass";
import { IUnit, ITerritory, IContext, ITerrStatus } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

export default class BoardClass {
  convoyGroups: ConvoyGroupClass[] = [];

  supplyCenters: TerritoryClass[] = [];

  territories: TerritoryClass[] = [];

  units: UnitClass[] = [];

  constructor(
    public context: IContext,
    territories: ITerritory[],
    terrStatus: ITerrStatus[],
    units: IUnit[],
  ) {
    /**
     * to reduce time, any territories that is either parent or child are stored and processed later in line 64
     */
    const coastParents: TerritoryClass[] = [];
    const coastChildren: TerritoryClass[] = [];

    this.territories = territories.map((territory) => {
      const curTerrStatus = terrStatus.find((ts) => ts.id === territory.id);

      let curTerritory = new TerritoryClass(territory);

      if (curTerrStatus) {
        curTerritory = new TerritoryClass(territory, curTerrStatus);

        let curUnit;
        if (curTerrStatus.unitID) {
          const unitData = units.find((u) => u.id === curTerrStatus.unitID);

          curUnit = unitData ? new UnitClass(unitData) : null;
        }

        if (curUnit) {
          curTerritory.setUnit(curUnit);

          curUnit.setTerritory(curTerritory);

          this.units.push(curUnit);
        }
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

    if (context.phase === "Builds") {
      this.territories.forEach((t) => {
        if (
          t.coastParent.supply &&
          t.coastParent.countryID === context.countryID &&
          t.coastParent.ownerCountryID === context.countryID &&
          !t.coastParent.Unit
        ) {
          this.supplyCenters.push(t);
        }
      });
    }
  }

  findUnitByID(unitID: string) {
    return this.units.find((unit) => unit.id === unitID);
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

  /**
   * Get all territories given unit can move to. Not including convoy move
   */
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

  /**
   * Get all territories given unit(Army) can move to. Including convoyable territories.
   */
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

  /**
   * Get all units on movable territories. (can support move to)
   */
  getMovableUnits(unit: UnitClass) {
    return this.getMovableTerritories(unit).reduce((acc: UnitClass[], cur) => {
      if (cur.coastParent.Unit) {
        acc.push(cur.coastParent.Unit);
      }
      return acc;
    }, []);
  }

  /**
   * Can given unit move to the target territory. Not including convoy move
   */
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

  /**
   * Can given unit move to the target territory. Including convoy move
   */
  static canConvoyTo(targetTerritory: TerritoryClass, unit: UnitClass) {
    if (unit.type === "Army") {
      if (targetTerritory.id === unit.Territory.id) return false;

      /**
       * Unit is in a convoy group. Unit can move to target territory via convoy move.
       */
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
    if ((unit.type === "Army" && !b.a) || (unit.type === "Fleet" && !b.f)) {
      return false;
    }

    return true;
  }
}
