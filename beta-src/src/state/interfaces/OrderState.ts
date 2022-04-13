import Territory from "../../enums/map/variants/classic/Territory";
import OrderType from "../../types/state/OrderType";

export interface OrderState {
  inProgress: boolean;
  unitID: string;
  onTerritory: Territory;
  type?: OrderType;
}

export default OrderState;
