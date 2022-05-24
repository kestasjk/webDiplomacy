import Territory from "../../enums/map/variants/classic/Territory";
import OrderType from "../../types/state/OrderType";

export interface OrderState {
  inProgress: boolean;
  // method: "click" | "dblClick";
  onTerritory: Territory | null;
  orderID: string;
  subsequentClicks: OrderState[];
  toTerritory: Territory | null;
  type?: OrderType;
  unitID: string;
}

export default OrderState;
