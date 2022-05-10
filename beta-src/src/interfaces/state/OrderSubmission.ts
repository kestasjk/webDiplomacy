import { QueryParams } from "../../utils/api";
import UpdateOrder from "./UpdateOrder";

export default interface OrderSubmission {
  orderUpdates: UpdateOrder[];
  context: string;
  contextKey: string;
  queryParams?: QueryParams;
}
