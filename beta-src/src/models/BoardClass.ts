import { IBoard } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";

export default class BoardClass {
  territories: TerritoryClass[];

  units: UnitClass[];

  myUnits: UnitClass[];

  constructor({ territories, units, terrStatus }: IBoard) {
    this.units = [];
    this.myUnits = [];

    this.territories = territories.map((territory) => {
      const curTerrStatus = terrStatus.find((ts) => ts.id === territory.id);

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

  getBoarderUnits(territory: TerritoryClass) {
    const borderTerritories = this.getBorderTerritories(territory.coastParent);

    return borderTerritories.map((bt) => bt.Unit);
  }
}
