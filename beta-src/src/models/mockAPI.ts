import {
  territories,
  units,
  context,
  terrStatus,
  ordersData,
} from "./testData";

import {
  ITerritory,
  IUnit,
  ITerrStatus,
  IContext,
  IOrderData,
} from "./Interfaces";

interface IData {
  territories: ITerritory[];
  units: IUnit[];
  terrStatus: ITerrStatus[];
  context: IContext;
  ordersData: IOrderData[];
}

const getData = new Promise<IData>((resolve, reject) => {
  resolve({
    territories: Object.values(territories),
    terrStatus,
    context,
    units: Object.values(units),
    ordersData,
  });
  reject(new Error("failed"));
});

export default getData;
