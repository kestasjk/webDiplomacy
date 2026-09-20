import Territory from "../../enums/map/variants/classic/Territory";
import OrderType from "../../types/state/OrderType";

export interface OrderState {
  inProgress: boolean;
  orderID: string;
  type?: OrderType;
  unitID: string;
  fromTerrID: string;
  toTerrID: string;
  viaConvoy: string;
  countryID: string;
}

export default OrderState;
