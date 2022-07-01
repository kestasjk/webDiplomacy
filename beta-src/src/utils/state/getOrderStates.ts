import { OrderStatus } from "../../interfaces";

export default function getOrderStates(orderStatus: string): OrderStatus {
  /**
   * orderStates will be a string like "None,Saved,Completed,Ready"
   */
  const orderStates = orderStatus.split(",");
  return {
    None: orderStates.includes("None"),
    Saved: orderStates.includes("Saved"),
    Completed: orderStates.includes("Completed"),
    Ready: orderStates.includes("Ready"),
    Hidden: orderStates.includes("Hidden"),
  };
}
