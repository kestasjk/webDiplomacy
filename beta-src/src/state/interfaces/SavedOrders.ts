import { IOrderData } from "../../models/Interfaces";
import TerritoryClass from "../../models/TerritoryClass";

interface OrderMetaUpdate {
  type: string;
  toTerrID: string | null;
  viaConvoy?: string | null;
}

interface OrderMeta {
  allowedBorderCrossings?: TerritoryClass[];
  saved: boolean;
  update?: OrderMetaUpdate;
  originalOrder: IOrderData;
}

export interface EditOrderMeta {
  [key: string]: {
    saved?: boolean;
    update?: OrderMetaUpdate;
    allowedBorderCrossings?: TerritoryClass[];
    originalOrder?: IOrderData;
  };
}

interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
