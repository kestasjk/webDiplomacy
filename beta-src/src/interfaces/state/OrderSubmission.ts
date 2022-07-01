import { QueryParams } from "../../utils/api";
import UpdateOrder from "./UpdateOrder";

export type OrderSubmissionUserIntent = "saving" | "readying" | "unreadying"

export default interface OrderSubmission {
  orderUpdates: UpdateOrder[];
  context: string;
  contextKey: string;
  queryParams?: QueryParams;
  userIntent: OrderSubmissionUserIntent;
}
