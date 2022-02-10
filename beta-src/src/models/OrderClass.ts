import BoardClass from "./BoardClass";
import TerritoryClass from "./TerritoryClass";
import { IOrder, IOrderData } from "./Interfaces";
import UnitClass from "./UnitClass";

export default class OrderClass implements IOrder {
  // eslint-disable-next-line no-useless-constructor
  constructor(
    public board: BoardClass,
    public unit: UnitClass,
    public orderData: IOrderData,
  ) {}

  getMoveChoices() {
    const choices = this.board
      .getMovableTerritories(this.unit)
      .map((movableTerritories) => movableTerritories.id);

    if (this.unit.convoyLink && this.unit.type === "Army") {
      const convoyableTerritories = this.unit.ConvoyGroup.coasts.reduce(
        (acc: string[], cur) => {
          if (BoardClass.canConvoyTo(cur, this.unit)) {
            acc.push(cur.id);
          }

          return acc;
        },
        [],
      );
      return Array.from(new Set(choices.concat(convoyableTerritories)));
    }

    return choices;
  }

  // Names of units territories I can support-hold
  getSupportHoldChoices() {
    const supportHoldChoices = this.board
      .getMovableUnits(this.unit)
      .reduce((acc: string[], cur) => {
        acc.push(cur.Territory.coastParent.id);

        return acc;
      }, []);

    return Array.from(new Set(supportHoldChoices));
  }

  // Names of territories I can support-move to (places I can support an attack to)
  getSupportMoveToChoices() {
    return this.board
      .getMovableTerritories(this.unit)
      .map((movableTerritory) => movableTerritory.coastParent.id);
  }

  // Names of territories containing units which I can support into the given Territory (fromTerrID)
  getSupportMoveFromChoices(againstTerritory: TerritoryClass) {
    // Essentially a list of units which can move into the given territory

    // Units bordering the given territory which can move into it
    let possibleUnits = this.board
      .getBorderUnits(againstTerritory.coastParent)
      .filter((borderUnit) => {
        return this.board.canMoveInto(borderUnit, againstTerritory);
      });
    // var PossibleUnits = AgainstTerritory.coastParent
    //   .getBorderUnits()
    //   .select(function (u) {
    //     return u.canMoveInto(AgainstTerritory);
    //   }, this);

    // Armies that could be convoyed into the given territory
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
        this.unit.type === "Fleet" &&
        this.unit.ConvoyGroup.coasts
          .map((coast) => coast.id)
          .includes(againstTerritory.id)
      ) {
        // Make sure ConvoyArmies contains no armies which can only reach AgainstTerritory
        // via a convoy containing this fleet.
        ConvoyArmies = againstTerritory.ConvoyGroup.armies.filter(
          (convoyArmy) => {
            // var path = AgainstTerritory.ConvoyGroup.pathArmyToCoastWithoutFleet(
            //   ConvoyArmy.Territory,
            //   AgainstTerritory,
            //   this.Territory,
            // );

            return !!againstTerritory.ConvoyGroup.pathArmyToCoastWithoutFleet(
              convoyArmy.Territory,
              againstTerritory,
              this.unit.Territory,
            );
          },
        );
      } else {
        ConvoyArmies = againstTerritory.ConvoyGroup.Armies;
      }

      //   this.convoyOptions =
      //     AgainstTerritory.ConvoyGroup.Armies.pluck("Territory").pluck("id");

      possibleUnits = possibleUnits.concat(ConvoyArmies);
      // } else {
      //   this.convoyOptions = [];
      // }
    }

    return Array.from(
      new Set(
        possibleUnits.map(
          (possibleUnit) => possibleUnit.Territory.coastParent.id,
        ),
      ),
    ).filter((possibleUnitTerritory) => {
      // true if its not current territory and not agaianst territory
      return (
        possibleUnitTerritory !== this.unit.Territory.coastParent.id &&
        possibleUnitTerritory !== againstTerritory.id
      );
    });
  }

  // Coasts which a fleet could convoy to
  getConvoyToChoices() {
    if (this.unit.convoyLink) {
      return this.unit.ConvoyGroup.coasts.map((coast) => coast.id);
    }

    return [];
  }

  // Coasts which a fleet could convoy from
  getConvoyFromChoices(toTerritory: TerritoryClass) {
    if (this.unit.convoyLink) {
      return this.unit.ConvoyGroup.armies.filter((convoyArmy) => {
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
