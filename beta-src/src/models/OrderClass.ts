import BoardClass from "./BoardClass";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

import { IOrderData } from "./Interfaces";
import { UnitType } from "./enums";

export default class OrderClass {
  // eslint-disable-next-line no-useless-constructor
  constructor(
    public board: BoardClass,
    public orderData: IOrderData,
    public unit: UnitClass,
  ) {}

  /**
   *
   * @returns movable territories including convoyable coasts
   */
  getMoveChoices(): TerritoryClass[] {
    const choices = this.board
      .getMovableTerritories(this.unit)
      .map((movableTerritories) => movableTerritories);

    if (this.unit.convoyLink && this.unit.type === UnitType.Army) {
      const convoyableTerritories = Array.from(
        this.unit.ConvoyGroup.coasts,
      ).reduce((acc: TerritoryClass[], cur) => {
        if (BoardClass.canConvoyTo(cur, this.unit)) {
          acc.push(cur);
        }

        return acc;
      }, []);

      return Array.from(new Set(choices.concat(convoyableTerritories)));
    }

    return choices;
  }

  /**
   *
   * @returns array of adjacent units
   */
  getSupportHoldChoices(): TerritoryClass[] {
    const movableUnits = this.board.getMovableUnits(this.unit);

    const supportHoldChoices = movableUnits.reduce(
      (acc: TerritoryClass[], cur) => {
        if (cur.Territory.coastParent) {
          acc.push(cur.Territory.coastParent);
        }

        return acc;
      },
      [],
    );

    return Array.from(new Set(supportHoldChoices));
  }

  /**
   *
   * @returns array of adjacent territories
   */
  getSupportMoveToChoices(): TerritoryClass[] {
    const movableTerritories = this.board.getMovableTerritories(this.unit);

    return movableTerritories.map((mt) => mt.coastParent);
  }

  /**
   *
   * @param againstTerritory from supportMoveToChoices
   * @returns array of territories that have unit which can move into the given territory
   */
  getSupportMoveFromChoices(
    againstTerritory: TerritoryClass,
  ): TerritoryClass[] {
    let possibleUnits = this.board
      .getBorderUnits(againstTerritory.coastParent)
      .filter((bu) => {
        return bu && this.board.canMoveInto(bu, againstTerritory);
      });

    if (againstTerritory.convoyLink) {
      /*
       * Resource intensive extra check, unnecessary 99% of the time. Leaving this disabled
       * means when an invalid support move is selected as a fleet the choice is undone once
       * it is selected and put through the check below.
       *
       */
      let ConvoyArmies;

      if (
        this.unit.convoyLink &&
        this.unit.type === UnitType.Fleet &&
        Array.from(this.unit.ConvoyGroup.coasts)
          .map((coast) => coast.id)
          .includes(againstTerritory.id)
      ) {
        ConvoyArmies = Array.from(againstTerritory.ConvoyGroup.armies).filter(
          (convoyArmy) => {
            return !!againstTerritory.ConvoyGroup.pathArmyToCoastWithoutFleet(
              convoyArmy.Territory,
              againstTerritory,
              this.unit.Territory,
            );
          },
        );
      } else {
        ConvoyArmies = againstTerritory.ConvoyGroup.armies;
      }

      possibleUnits = [...possibleUnits, ...ConvoyArmies];
    }

    return Array.from(
      new Set(
        possibleUnits.map((possibleUnit) => possibleUnit.Territory.coastParent),
      ),
    ).filter((possibleUnitTerritory) => {
      return (
        possibleUnitTerritory.id !== this.unit.Territory.coastParentID &&
        possibleUnitTerritory.id !== againstTerritory.id
      );
    });
  }

  /**
   *
   * @returns array of coasts' id which a fleet can convoy an army to
   */
  getConvoyToChoices(): string[] {
    if (this.unit.convoyLink) {
      return Array.from(this.unit.ConvoyGroup.coasts).map((coast) => coast.id);
    }

    return [];
  }

  /**
   *
   * @param toTerritory convoy destination
   * @returns array of coasts that have army which a fleet can convoy from
   */
  getConvoyFromChoices(toTerritory: TerritoryClass): UnitClass[] {
    if (this.unit.convoyLink) {
      return Array.from(this.unit.ConvoyGroup.armies).filter((convoyArmy) => {
        if (toTerritory.id === convoyArmy.Territory.id) {
          return false;
        }

        return !!toTerritory.ConvoyGroup.pathArmyToCoastWithFleet(
          convoyArmy.Territory,
          toTerritory,
          this.unit.Territory,
        );
      });
    }

    return [];
  }
}
