import * as React from "react";
import BoardClass from "./BoardClass";
import OrderClass from "./OrderClass";

import getData from "./mockAPI";

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

const defaultOptions = [
  { name: "Hold" },
  { name: "Move" },
  { name: "Support Hold" },
  { name: "Support Move" },
];

interface WDOrderInterfaceProps {
  userOrder: OrderClass;
}

const OrderInterface: React.FC<WDOrderInterfaceProps> = function ({
  userOrder,
}): React.ReactElement {
  /**
   * Bottom three will be used for order submission.
   */
  const [currOrder, setCurrOrder] = React.useState(userOrder);
  const [toTerrID, setToTerrID] = React.useState(userOrder.orderData.toTerrID);
  const [fromTerrID, setFromTerrID] = React.useState(
    userOrder.orderData.fromTerrID,
  );

  const [optionOne, setOptionOne] = React.useState(defaultOptions);
  const [optionOneVal, setOptionOneVal] = React.useState(
    defaultOptions.findIndex((o) => o.name === currOrder.orderData.type),
  );
  const [optionTwo, setOptionTwo] = React.useState<any[]>([]);
  const [optionTwoVal, setOptionTwoVal] = React.useState<number>();
  const [optionThree, setOptionThree] = React.useState<any[]>([]);
  const [optionThreeVal, setOptionThreeVal] = React.useState<number>();

  React.useEffect(() => {
    setOptionTwo([]);
    setOptionThree([]);
    setOptionTwoVal(0);

    if (optionOneVal === 0) {
      setOptionTwo([]);
    }
    if (optionOneVal === 1) {
      setOptionTwo(currOrder.getMoveChoices());
    }
    if (optionOneVal === 2) {
      setOptionTwo(currOrder.getSupportHoldChoices());
    }
    if (optionOneVal === 3) {
      setOptionTwo(currOrder.getSupportMoveToChoices());
    }
  }, [optionOneVal]);

  React.useEffect(() => {
    setOptionThree([]);
    setOptionThreeVal(0);
    if (optionTwoVal) {
      if (optionOneVal === 3) {
        setOptionThree(
          currOrder.getSupportMoveFromChoices(optionTwo[optionTwoVal]),
        );
      }
    }
  }, [optionTwoVal]);

  const renderOptions = (options: any[]) => {
    return options.map((option, idx) => (
      <option value={idx}>{option.name}</option>
    ));
  };

  return (
    <div>
      <div style={{ width: "250px", display: "inline-block" }}>
        <span style={{ color: "red" }}>{currOrder.unit.type}</span> In{" "}
        <span style={{ color: "blue" }}>{currOrder.unit.Territory.name}</span>{" "}
      </div>
      can{" "}
      <select
        value={optionOneVal}
        onChange={(e) => setOptionOneVal(Number(e.target.value))}
      >
        {renderOptions(optionOne)}
      </select>
      {optionTwo.length ? (
        <span>
          {" "}
          to{" "}
          <select
            value={optionTwoVal}
            onChange={(e) => setOptionTwoVal(Number(e.target.value))}
          >
            {renderOptions(optionTwo)}
          </select>
        </span>
      ) : null}
      {optionThree.length ? (
        <span>
          {" "}
          unit from{" "}
          <select
            value={optionThreeVal}
            onChange={(e) => setOptionThreeVal(Number(e.target.value))}
          >
            {renderOptions(optionThree)}
          </select>
        </span>
      ) : null}
    </div>
  );
};

const Dummy: React.FC = function (): React.ReactElement {
  const [board, setBoard] = React.useState({});
  const [userOrders, setUserOrders] = React.useState<OrderClass[]>([]);

  React.useEffect(() => {
    getData.then(({ context, territories, terrStatus, units, ordersData }) => {
      const newBoard = new BoardClass(
        context,
        Object.values(territories),
        terrStatus,
        Object.values(units),
      );

      const newOrders: OrderClass[] = [];

      ordersData.forEach((order) => {
        const orderUnit = newBoard.findUnitByID(order.unitID);
        if (orderUnit) {
          newOrders.push(new OrderClass(newBoard, order, orderUnit));
        }
      });
      setBoard(newBoard);
      setUserOrders(newOrders);
    });
  }, []);

  return (
    <div>
      {userOrders.map((userOrder) => {
        return <OrderInterface userOrder={userOrder} />;
      })}
    </div>
  );
};

export default Dummy;
