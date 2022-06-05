import ContextVar from "./ContextVar";
import SavedOrder from "./SavedOrder";

export default interface SavedOrdersConfirmation {
  invalid: boolean;
  notice: string;
  orders: SavedOrder;
  // statusIcon: string;
  // statusText: string;
  newContext?: ContextVar["context"];
  newContextKey?: ContextVar["contextKey"];
}
