import { EditOrderMeta } from "../../state/interfaces/SavedOrders";

export default interface UpdateOrdersMetaAction {
  type: string;
  payload: EditOrderMeta;
}
