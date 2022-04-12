import Territory from "../../enums/map/variants/classic/Territory";
import OrderType from "../../types/state/OrderType";

export interface OrderState {
  inProgress: boolean;
  onTerritory: Territory | null;
  orderID: string;
  toTerritory: Territory | null;
  type?: OrderType;
  unitID: string;
}

export default OrderState;
