import { IOrderData } from "../../models/Interfaces";

export interface OrderMetaUpdate {
  convoyPath?: string[];
  type: string;
  toTerrID: string | null;
  fromTerrID?: string | null;
  viaConvoy?: string | null;
}

interface SharedMeta {
  originalOrder?: IOrderData;
  update?: OrderMetaUpdate;
}

export interface OrderMeta extends SharedMeta {
  saved: boolean;
}

export interface EditOrder extends SharedMeta {
  saved?: boolean;
}

export interface EditOrderMeta {
  [key: string]: EditOrder;
}

// The key on this interface is Order ID.
interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
