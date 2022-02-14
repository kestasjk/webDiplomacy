import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";
import BoardClass from "./BoardClass";

export default class ConvoyGroupClass {
  armies: Set<UnitClass> = new Set();

  coasts: Set<TerritoryClass> = new Set();

  fleets: Set<UnitClass> = new Set();

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

  loadFleet(fleet: UnitClass) {
    if (fleet.convoyLink) {
      return;
    }

    if (fleet.Territory.type !== "Sea") {
      return;
    }

    fleet.setConvoyLink();
    fleet.setConvoyGroup(this);

    this.fleets.add(fleet);

    this.board.getBorderTerritories(fleet.Territory).forEach((bt) => {
      if (bt.type === "Sea" && bt.Unit) {
        this.loadFleet(bt.Unit);
      }
    });
  }

  loadCoasts() {
    this.fleets.forEach((f) => {
      this.board.getBorderTerritories(f.Territory).forEach((bt) => {
        if (bt.type !== "Coast") {
          return;
        }

        if (this.coasts.has(bt)) {
          return;
        }

        bt.setConvoyGroups(this);

        this.coasts.add(bt);

        if (bt.Unit && bt.Unit.type === "Army") {
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

      if (c.Unit && c.Unit.type === "Army") {
        c.Unit.setConvoyLink();
        c.Unit.setConvoyGroup(c.ConvoyGroup);
      }
    });
  }
}
