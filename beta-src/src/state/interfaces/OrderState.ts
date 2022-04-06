import Territory from "../../enums/map/variants/classic/Territory";

export interface OrderState {
  inProgress: boolean;
  unitID: string;
  onTerritory: Territory;
}

export default OrderState;
