import * as React from "react";
import BoardClass from "./BoardClass";
import OrderClass from "./OrderClass";

import { boardData, units, context, terrStatus, ordersData } from "./testData";

const METHODS = Object.freeze({
  MOVETO: "move to",
  SUPPORTHOLD: "support hold",
  SUPPORTMOVETO: "support move to",
  CONVOYTO: "convoy to",
});

const Dummy: React.FC = function (): React.ReactElement {
  const board = new BoardClass(
    context,
    Object.values(boardData),
    terrStatus,
    Object.values(units),
  );

  const findUnit = (unitID) => {
    return board.units.find((unit) => unit.id === unitID);
  };

  const orders = ordersData.map((order) => {
    const unit = findUnit(order.unitID);

    return unit ? new OrderClass(board, order, unit) : null;
  });

  /**
   * list of order generation methods
   *  getMoveChoices();
      getSupportHoldChoices();
      getSupportMoveToChoices();
      getConvoyToChoices();

      These two methods still need to be refactored.
      getSupportMoveFromChoices();
      getConvoyFromChoices();
   */

  const orderMethodsTest = (type, order) => {
    const helper = () => {
      switch (type) {
        case "move to":
          return order
            ?.getMoveChoices()
            .map((id) => (
              <div>{board.territories.find((t) => t.id === id)?.name}</div>
            ));
          break;
        case "support hold":
          return order
            ?.getSupportHoldChoices()
            .map((id) => (
              <div>{board.territories.find((t) => t.id === id)?.name}</div>
            ));
          break;
        case "support move to":
          return order
            ?.getSupportMoveToChoices()
            .map((id) => (
              <div>{board.territories.find((t) => t.id === id)?.name}</div>
            ));
          break;
        case "convoy to":
          return order
            ?.getConvoyToChoices()
            .map((id) => (
              <div>{board.territories.find((t) => t.id === id)?.name}</div>
            ));
          break;
        default:
          return null;
      }
    };

    return (
      <div style={{ marginBottom: "10px" }}>
        <div style={{ fontWeight: "bold" }}>
          {order?.unit.type} UNIT {order?.unit.id} in{" "}
          {order?.unit.Territory.name}
        </div>
        <div style={{ color: "red" }}>can {type}</div>
        {helper()}
      </div>
    );
  };

  return (
    <div>
      {orders.map((order) => {
        return orderMethodsTest(METHODS.MOVETO, order);
      })}
    </div>
  );
};

export default Dummy;
