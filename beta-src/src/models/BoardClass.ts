import ConvoyGroupClass from "./ConvoyGroupClass";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

import { IUnit, ITerritory, IContext, IProvinceStatus } from "./Interfaces";
import { CoastType, GamePhaseType, UnitType, TerritoryType } from "./enums";

export default class BoardClass {
  convoyGroups: ConvoyGroupClass[] = [];

  supplyCenters: TerritoryClass[] = [];

  territories: TerritoryClass[] = [];

  units: UnitClass[] = [];

  constructor(
    public context: IContext,
    territories: ITerritory[],
    terrStatus: IProvinceStatus[],
    units: IUnit[],
  ) {
    const coastParents: TerritoryClass[] = [];
    const coastChildren: TerritoryClass[] = [];

    this.territories = territories.map((territory) => {
      const curTerrStatus = terrStatus.find((ts) => ts.id === territory.id);

      let currTerritory = new TerritoryClass(territory, this);

      if (curTerrStatus) {
        currTerritory = new TerritoryClass(territory, this, curTerrStatus);
      }

      // n.b. currTerrStatus.unit does not handle coasts!
      const unitData = units.find((u) => u.terrID === currTerritory.id);
      const currUnit = unitData ? new UnitClass(unitData) : null;

      if (currUnit) {
        currTerritory.setUnit(currUnit);

        currUnit.setTerritory(currTerritory);

        this.units.push(currUnit);
      }

      if (currTerritory.coast === CoastType.Parent) {
        coastParents.push(currTerritory);
      }

      if (currTerritory.coast === CoastType.Child) {
        coastChildren.push(currTerritory);
      }

      if (currTerritory.coastParentID === currTerritory.id) {
        currTerritory.coastParent = currTerritory;
      }

      return currTerritory;
    });

    coastChildren.forEach((cc) => {
      const parent = coastParents.find((cp) => cp.id === cc.coastParentID);
      if (parent) {
        cc.setCoastParent(parent);
        parent.addCoastChild(cc);
      }
    });

    /**
     * load and initialize convoyGroup.
     * convoyGroup has
     * fleets that are adjacent
     * coasts that are adjacent above fleets
     * armies that are placed in above coasts
     */
    if (context.phase === GamePhaseType.Diplomacy) {
      this.units.forEach((u) => {
        if (
          u.type === UnitType.Fleet &&
          u.Territory.type === TerritoryType.Sea
        ) {
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

    if (context.phase === GamePhaseType.Builds) {
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

  /**
   *
   * @param unitID
   * @returns {UnitClass}
   */
  findUnitByID(unitID: string): UnitClass | undefined {
    return this.units.find((unit) => unit.id === unitID);
  }

  /**
   *
   * @param territoryID
   * @returns {TerritoryClass}
   */
  findTerritoryByID(territoryID: string): TerritoryClass | undefined {
    return this.territories.find((t) => t.id === territoryID);
  }

  /**
   * finds adjacent territories
   * @param territory
   * @returns {TerritoryClass[]}
   */
  getBorderTerritories(territory: TerritoryClass): TerritoryClass[] | [] {
    const borderIDs = territory.Borders.map((b) => b.id);

    return this.territories.filter((t) => borderIDs.includes(`${t.id}`));
  }

  /**
   * find units on border territories
   * @param territory
   * @returns {UnitClass[]}
   */
  getBorderUnits(territory: TerritoryClass): UnitClass[] {
    return this.getBorderTerritories(territory.coastParent).map(
      (bt: TerritoryClass) => bt.Unit,
    );
  }

  /**
   * Get all territories given unit can move to. Not including convoy move
   * @param unit
   * @returns {TerritoryClass}
   */
  getMovableTerritories(unit: UnitClass): TerritoryClass[] {
    return unit.Territory.CoastalBorders.reduce(
      (acc: TerritoryClass[], cur) => {
        if (unit.canCrossBorder(cur)) {
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
   * @param unit
   * @returns {TerritoryClass[]}
   */
  getReachableTerritories(unit: UnitClass): TerritoryClass[] {
    if (!unit.convoyLink && unit.type !== UnitType.Army) {
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
   * @param unit
   * @returns {UnitClass[]}
   */
  getMovableUnits(unit: UnitClass): UnitClass[] {
    const movableTerritories = this.getMovableTerritories(unit);

    // tricky: we can support units in the territory itself *or*
    // any coasts of the territory.
    // iterate over a copy of movableTerritories so we can insert
    [...movableTerritories].forEach((terr) => {
      terr.coastChildren.forEach((child) => {
        movableTerritories.push(child);
      });
      // also need to make sure you iterate over the parent's children
      // so that you can include coasts laterally rather than just
      // parent <-> child
      terr.coastParent.coastChildren.forEach((child) => {
        movableTerritories.push(child);
      });
    });

    return movableTerritories.reduce((acc: UnitClass[], cur) => {
      if (cur.Unit) {
        acc.push(cur.Unit);
      }
      if (cur.coastParent.Unit) {
        acc.push(cur.coastParent.Unit);
      }
      return acc;
    }, []);
  }

  /**
   * Can given unit move to the target territory. Not including convoy move
   * @param unit
   * @param targetTerritory
   * @returns {boolean}
   */
  canMoveInto(unit: UnitClass, targetTerritory: TerritoryClass): boolean {
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
   * @param targetTerritory
   * @param unit
   * @returns {boolean}
   */
  static canConvoyTo(
    targetTerritory: TerritoryClass,
    unit: UnitClass,
  ): boolean {
    if (unit.type === UnitType.Army) {
      if (targetTerritory.id === unit.Territory.id) {
        return false;
      }

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
}
