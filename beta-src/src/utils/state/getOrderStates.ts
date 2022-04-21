interface OrderStates {
  None: boolean;
  Saved: boolean;
  Completed: boolean;
  Ready: boolean;
}

export default function getOrderStates(orderStatus: string): OrderStates {
  /**
   * orderStates will be a string like "None,Saved,Completed,Ready"
   */
  const orderStates = orderStatus.split(",");
  return {
    None: orderStates.includes("None"),
    Saved: orderStates.includes("Saved"),
    Completed: orderStates.includes("Completed"),
    Ready: orderStates.includes("Ready"),
  };
}
