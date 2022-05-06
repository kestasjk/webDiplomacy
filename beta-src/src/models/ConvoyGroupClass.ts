import BoardClass from "./BoardClass";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

import NodeSetClass from "./NodeSetClass";

import { UnitType, TerritoryType } from "./enums";

export default class ConvoyGroupClass {
  armies: Set<UnitClass> = new Set();

  coasts: Set<TerritoryClass> = new Set();

  fleets: Set<UnitClass> = new Set();

  Path;

  // eslint-disable-next-line no-useless-constructor
  constructor(public board: BoardClass) {}

  setArmies(armies: Set<UnitClass>) {
    this.armies = armies;
  }

  setCoasts(coasts: Set<TerritoryClass>) {
    this.coasts = coasts;
  }

  setFleets(fleets: Set<UnitClass>) {
    this.fleets = fleets;
  }

  /**
   *
   * @param fleet
   * @returns
   */
  loadFleet(fleet: UnitClass) {
    if (fleet.convoyLink || fleet.Territory.type !== TerritoryType.Sea) {
      return;
    }

    fleet.setConvoyLink();
    fleet.setConvoyGroup(this);

    this.fleets.add(fleet);

    this.board.getBorderTerritories(fleet.Territory).forEach((bt) => {
      if (bt.type === TerritoryType.Sea && bt.Unit) {
        this.loadFleet(bt.Unit);
      }
    });
  }

  loadCoasts() {
    this.fleets.forEach((f) => {
      this.board.getBorderTerritories(f.Territory).forEach((bt) => {
        if (bt.type !== TerritoryType.Coast || this.coasts.has(bt)) {
          return;
        }

        bt.setConvoyGroups(this);

        this.coasts.add(bt);

        if (bt.Unit && bt.Unit.type === UnitType.Army) {
          this.armies.add(bt.Unit);
        }
      });
    });
  }

  linkGroups() {
    this.coasts.forEach((c) => {
      if (c.convoyLink) {
        return;
      }

      if (c.ConvoyGroups.length === 1) {
        c.setConvoyGroup(c.ConvoyGroups[0]);
      } else {
        const newConvoyGroup = new ConvoyGroupClass(this.board);
        c.setConvoyGroup(newConvoyGroup);

        c.ConvoyGroups.forEach((cg) => {
          c.ConvoyGroup.setArmies(
            new Set([
              ...Array.from(c.ConvoyGroup.armies),
              ...Array.from(cg.armies),
            ]),
          );
          c.ConvoyGroup.setCoasts(
            new Set([
              ...Array.from(c.ConvoyGroup.coasts),
              ...Array.from(cg.coasts),
            ]),
          );
          c.ConvoyGroup.setFleets(
            new Set([
              ...Array.from(c.ConvoyGroup.fleets),
              ...Array.from(cg.fleets),
            ]),
          );
        });
      }

      c.setConvoyLink();

      if (c.Unit && c.Unit.type === UnitType.Army) {
        c.Unit.setConvoyLink();
        c.Unit.setConvoyGroup(c.ConvoyGroup);
      }
    });
  }

  nodeSetClass() {
    const ns = new NodeSetClass();
    ns.routeSetLoad(this);
    return ns;
  }

  pathArmyToCoast(StartTerr, EndTerr) {
    const ns = this.nodeSetClass();
    ns.routeSetStart(
      StartTerr,
      (EndNode) => {
        return EndNode?.id === EndTerr.id;
      },
      (AllNode) => {
        return AllNode.type === TerritoryType.Sea;
      },
      () => {
        return true;
      },
    );
    return ns.Path;
  }

  pathArmyToCoastWithoutFleet(StartTerr, EndTerr, WithoutFleetTerr) {
    const ns = this.nodeSetClass();
    ns.routeSetStart(
      StartTerr,
      (EndNode) => {
        return EndNode?.id === EndTerr.id;
      },
      (AllNode) => {
        return (
          AllNode.type === TerritoryType.Sea &&
          AllNode.id !== WithoutFleetTerr.id
        );
      },
      () => {
        return true;
      },
    );
    return ns.Path;
  }

  pathArmyToCoastWithFleet(StartTerr, EndTerr, WithFleetTerr) {
    const ns = this.nodeSetClass();
    ns.routeSetStart(
      StartTerr,
      (EndNode) => {
        return EndNode?.id === EndTerr.id;
      },
      (AllNode) => {
        return AllNode.type === TerritoryType.Sea;
      },
      (AnyNode) => {
        return AnyNode.id === WithFleetTerr.id;
      },
    );
    console.log({ ns });
    return ns.Path;
  }
}
