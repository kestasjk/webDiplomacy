import { IBoard } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

// this is will be used for accessing all necessary data and methods

export default class BoardClass {
  // all territories.
  territories: TerritoryClass[];

  // includes entire units currently placed in the board.
  units: UnitClass[];

  // This will be used for order generation.
  myUnits: UnitClass[];

  constructor({ territories, units, terrStatus }: IBoard) {
    this.units = [];
    this.myUnits = [];

    // TerritoryClass instantiation
    this.territories = territories.map((territory) => {
      // finding terrStatus(from DB) matches to the current territory
      // We can simply use an index if both Territories and TerrStatus is ordered.
      const curTerrStatus = terrStatus.find((ts) => ts.id === territory.id);

      const territoryObj = {
        ...territory,
        // this is because of the coasts in Spain, Bulgaria and ST. Petersburg.
        // it's default to itself
        coastParent: territory,
        ...curTerrStatus,
      };

      // I'm creating an instance of UnitClass here to reduce the time complexity.
      // We can assume that each territory can have 1 unit at most and this will create all units in the board.
      const curUnit = curTerrStatus?.unitID
        ? new UnitClass(units[curTerrStatus.unitID])
        : null;

      if (curUnit) {
        this.units.push(curUnit);

        territoryObj.Unit = curUnit;
        territoryObj.unitID = curUnit.id;

        // we can use context instead of territory
        if (curUnit.countryID === territory.countryID) {
          this.myUnits.push(curUnit);
        }
      }

      // coasts in Spain, Bulgaria and ST. Petersburg.
      if (territory.coastParentID !== territory.id) {
        const newCoastParent =
          territories.find((t) => t.id === territory.coastParentID) ||
          territory;

        // basically mutating values to match it's parent.
        // I think we can just change the value in the DB and skip this part. Not 100% sure tho.
        territoryObj.coastParent = newCoastParent;
        territoryObj.supply = newCoastParent?.supply;
        territoryObj.Borders = newCoastParent?.Borders;
      }

      return new TerritoryClass(territoryObj);
    });
  }

  // this is part of the order generation. Basically find all the territories around clicked one.
  getBorderTerritories(territory: TerritoryClass) {
    const borderIDs = territory.Borders.map((b) => b.id);

    return this.territories.filter((t) => borderIDs.includes(`${t.id}`));
  }

  // use above methods to find a territories around first and get units placed on them
  getBoarderUnits(territory: TerritoryClass) {
    const borderTerritories = this.getBorderTerritories(territory.coastParent);

    return borderTerritories.map((bt) => bt.Unit);
  }
}
